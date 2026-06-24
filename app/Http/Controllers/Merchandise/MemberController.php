<?php

namespace App\Http\Controllers\Merchandise;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('membership_level', $request->level);
        }

        $members = $query->orderBy('name', 'asc')->paginate(15);

        return view('members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'membership_level' => 'nullable|in:bronze,silver,gold,platinum',
            'is_active' => 'boolean',
        ]);

        $data = $request->only('name', 'phone', 'email', 'address', 'birth_date', 'gender', 'membership_level');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->filled('id')) {
            $member = Member::findOrFail($request->id);
            $old = $member->toArray();
            $member->update($data);
            app(ActivityLogger::class)->log('updated', $member, "Mengupdate member {$member->name}", $old, $member->toArray());
        } else {
            $data['code'] = $this->generateCode();
            $data['points'] = 0;
            $member = Member::create($data);
            app(ActivityLogger::class)->log('created', $member, "Membuat member {$member->name}");
        }

        return redirect()->route('merchandise.members.index')
            ->with('success', 'Member berhasil disimpan.');
    }

    public function show(Member $member)
    {
        $member->load(['sales' => function ($q) {
            $q->latest()->limit(20);
        }, 'sales.items.product']);

        $totalSales = $member->sales()->count();
        $totalSpent = $member->total_spent;

        return view('members.show', compact('member', 'totalSales', 'totalSpent'));
    }

    public function destroy(Member $member)
    {
        if ($member->sales()->count() > 0) {
            return redirect()->route('merchandise.members.index')
                ->with('error', 'Member tidak dapat dihapus karena sudah memiliki transaksi penjualan.');
        }

        if ($member->points > 0) {
            return redirect()->route('merchandise.members.index')
                ->with('error', 'Member tidak dapat dihapus karena masih memiliki poin (' . number_format($member->points, 0, ',', '.') . ' poin).');
        }

        app(ActivityLogger::class)->log('deleted', $member, "Menghapus member {$member->name}");
        $member->delete();

        return redirect()->route('merchandise.members.index')
            ->with('success', 'Member berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $date = Carbon::today()->format('Ymd');
        $lastToday = Member::whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($lastToday && $lastToday->code) {
            $lastNumber = (int) substr($lastToday->code, -3);
            $number = $lastNumber + 1;
        }

        return 'MBR-' . $date . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
