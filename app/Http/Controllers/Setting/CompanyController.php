<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $company = CompanySetting::instance();

        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'receipt_message' => 'nullable|string',
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'logo' => 'nullable|max:2048',
        ]);

        $company = CompanySetting::instance();
        $data = $request->only([
            'company_name', 'phone', 'email', 'website', 'address',
            'city', 'province', 'postal_code', 'npwp', 'fax',
            'tax_rate', 'receipt_message', 'receipt_header', 'receipt_footer',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        $company->update($data);

        app(ActivityLogger::class)->log('updated', $company, "Mengupdate profil perusahaan");

        return redirect()->route('settings.company.index')
            ->with('success', 'Profil perusahaan berhasil disimpan.');
    }
}
