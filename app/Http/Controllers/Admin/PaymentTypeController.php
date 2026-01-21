<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    // MENAMPILKAN DAFTAR (READ)
    public function index()
    {
        $paymentTypes = PaymentType::latest()->get(); //mengambil seluruh data dari tabel payment_types
        return view('admin.payment_types.index', compact('paymentTypes'));
    }

    // HALAMAN CREATE
    public function create()
    {
        return view('admin.payment_types.create');
    }

    //PROSES SIMPAN
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name',
        ]);

        PaymentType::create([
            'name' => $request->name,
            'is_active' => true
        ]);

        return redirect()->route('admin.payment-types.index')
            ->with('success', 'Tipe pembayaran berhasil ditambahkan.');
    }

    //EDIT
    public function edit(PaymentType $paymentType)
    {
        return view('admin.payment_types.edit', compact('paymentType'));
    }

    //UPDATE
    public function update(Request $request, PaymentType $paymentType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name,' . $paymentType->id,
        ]);

        $paymentType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.payment-types.index')
            ->with('success', 'Tipe pembayaran berhasil diperbarui.');
    }

    //DELETE
    public function destroy(PaymentType $paymentType)
    {
        $paymentType->delete();
        return redirect()->route('admin.payment-types.index')
            ->with('success', 'Tipe pembayaran berhasil dihapus.');
    }
}
