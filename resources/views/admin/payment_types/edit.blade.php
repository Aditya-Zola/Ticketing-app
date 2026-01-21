<x-layouts.admin title="Edit Tipe Pembayaran">
    <div class="container mx-auto p-10">
        <h1 class="text-3xl font-semibold mb-6">Edit Tipe Pembayaran</h1>

        {{-- Card Form --}}
        <div class="bg-white rounded-box shadow-xs p-8 max-w-2xl">
            <form action="{{ route('admin.payment-types.update', $paymentType->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Input Nama --}}
                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text font-bold text-base">Nama Tipe Pembayaran</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $paymentType->name) }}"
                           class="input input-bordered w-full @error('name') input-error @enderror"
                           required />

                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('admin.payment-types.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary px-8">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
