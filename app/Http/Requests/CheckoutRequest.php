<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alamat' => 'required|string|max:500',
            'detail-alamat' => 'nullable|string|max:255',
            'kota' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'kode-pos' => 'required|digits:5',
            'bukti-pembayaran' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'alamat.required' => 'Alamat harus diisi.',
            'alamat.max' => 'Alamat terlalu panjang (maksimal :max karakter).',
            'detail-alamat.max' => 'Detail alamat terlalu panjang (maksimal :max karakter).',
            'kota.required' => 'Kota harus diisi.',
            'provinsi.required' => 'Provinsi harus diisi.',
            'kode-pos.required' => 'Kode pos harus diisi.',
            'kode-pos.digits' => 'Kode pos harus berupa 5 digit angka.',
            'bukti-pembayaran.required' => 'Bukti pembayaran harus diupload.',
            'bukti-pembayaran.file' => 'Bukti pembayaran harus berupa file.',
            'bukti-pembayaran.mimes' => 'Bukti pembayaran harus berupa file JPEG, PNG, JPG, atau PDF.',
            'bukti-pembayaran.max' => 'Bukti pembayaran tidak boleh lebih dari 10 MB.',
        ];
    }

    /** Gabungkan kolom alamat menjadi satu teks untuk disimpan di pesanan. */
    public function fullAddress(): string
    {
        return implode(', ', array_filter([
            $this->input('alamat'),
            $this->input('detail-alamat'),
            $this->input('kota'),
            $this->input('provinsi'),
            $this->input('kode-pos'),
        ]));
    }
}
