<?php

namespace App\Http\Controllers\Merchandise;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberCreditPayment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class MemberCreditController extends Controller
{
    public function index()
    {
        $members = Member::where('outstanding_balance', '>', 0)
            ->orderBy('outstanding_balance', 'desc')
            ->paginate(15);

        return view('members.credit-index', compact('members'));
    }

    public function create(Member $member)
    {
        $member->load('sales');
        return view('members.credit', compact('member'));
    }

    public function store(Request $request, Member $member)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $member->outstanding_balance,
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $amount = (float) $request->amount;

        MemberCreditPayment::create([
            'member_id' => $member->id,
            'amount' => $amount,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ]);

        $member->decrement('outstanding_balance', $amount);

        app(ActivityLogger::class)->log('credit_payment', $member, "Mencatat pembayaran piutang member {$member->name}");

        return redirect()->route('merchandise.members.show', $member)
            ->with('success', 'Pembayaran piutang berhasil dicatat.');
    }

    public function history(Member $member)
    {
        $payments = MemberCreditPayment::where('member_id', $member->id)
            ->with('user')
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('members.credit-history', compact('member', 'payments'));
    }
}
