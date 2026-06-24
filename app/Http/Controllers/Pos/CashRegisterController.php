<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = CashRegister::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $cashRegisters = $query->orderBy('name', 'asc')->paginate(15);

        return view('pos.cash-registers', compact('cashRegisters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->only('name', 'description');
        $data['is_active'] = $request->boolean('is_active', true);

        if (!$data['is_active'] && !$request->has('_is_active')) {
            $data['is_active'] = false;
        }

        if ($request->filled('id')) {
            $cashRegister = CashRegister::findOrFail($request->id);
            $cashRegister->update($data);
            $model = $cashRegister;
        } else {
            $data['code'] = 'REG-' . str_pad(CashRegister::max('id') + 1, 3, '0', STR_PAD_LEFT);
            $data['warehouse_id'] = 1;
            $model = CashRegister::create($data);
        }

        app(ActivityLogger::class)->log('cash_register_store', $model, "Membuat mesin kasir {$model->name}");

        return redirect()->route('pos.cash-registers.index')
            ->with('success', 'Kasir berhasil disimpan.');
    }

    public function destroy(CashRegister $cashRegister)
    {
        $name = $cashRegister->name;
        $cashRegister->delete();

        app(ActivityLogger::class)->log('cash_register_destroy', $cashRegister, "Menghapus mesin kasir {$name}");

        return redirect()->route('pos.cash-registers.index')
            ->with('success', 'Kasir berhasil dihapus.');
    }
}
