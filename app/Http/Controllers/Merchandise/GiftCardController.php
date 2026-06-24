<?php

namespace App\Http\Controllers\Merchandise;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class GiftCardController extends Controller
{
    public function index(Request $request)
    {
        $query = GiftCard::with('issuer');

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%");
        }

        $giftCards = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('settings.gift-cards.index', compact('giftCards'));
    }

    public function create()
    {
        return view('settings.gift-cards.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:gift_cards,code',
            'initial_balance' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $giftCard = GiftCard::create([
            'code' => $request->code,
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
            'expires_at' => $request->expires_at,
            'notes' => $request->notes,
            'issued_by' => auth()->id(),
            'is_active' => true,
        ]);

        app(ActivityLogger::class)->log('created', $giftCard, "Membuat gift card {$giftCard->code}");

        return redirect()->route('settings.gift-cards.index')
            ->with('success', 'Gift Card berhasil diterbitkan.');
    }

    public function edit(GiftCard $giftCard)
    {
        return view('settings.gift-cards.edit', compact('giftCard'));
    }

    public function update(Request $request, GiftCard $giftCard)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:gift_cards,code,' . $giftCard->id,
            'initial_balance' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only('code', 'initial_balance', 'expires_at', 'notes');
        $data['is_active'] = $request->boolean('is_active', true);

        $old = $giftCard->toArray();
        $giftCard->update($data);
        app(ActivityLogger::class)->log('updated', $giftCard, "Mengupdate gift card {$giftCard->code}", $old, $giftCard->toArray());

        return redirect()->route('settings.gift-cards.index')
            ->with('success', 'Gift Card berhasil diperbarui.');
    }

    public function destroy(GiftCard $giftCard)
    {
        app(ActivityLogger::class)->log('deleted', $giftCard, "Menghapus gift card {$giftCard->code}");
        $giftCard->delete();

        return redirect()->route('settings.gift-cards.index')
            ->with('success', 'Gift Card berhasil dihapus.');
    }

    public function topUp(Request $request, GiftCard $giftCard)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $giftCard->increment('current_balance', $request->amount);
        $giftCard->increment('initial_balance', $request->amount);

        app(ActivityLogger::class)->log('topup', $giftCard, "Topup gift card {$giftCard->code}");

        return redirect()->route('settings.gift-cards.index')
            ->with('success', 'Saldo Gift Card berhasil ditambahkan.');
    }
}
