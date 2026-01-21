<x-layouts.admin title="Manajemen Tipe Pembayaran">
    @if (session('success'))
        <div class="toast toast-bottom toast-center">
            <div class="alert alert-success">
                <span>{{ session('success') }}</span>
            </div>
        </div>

        <script>
        setTimeout(() => {
            document.querySelector('.toast')?.remove()
        }, 3000)
        </script>
    @endif

    <div class="container mx-auto p-10">
        {{-- Header & Tombol Tambah --}}
        <div class="flex">
            <h1 class="text-3xl font-semibold mb-4">Manajemen Tipe Pembayaran</h1>
            <a href="{{ route('admin.payment-types.create') }}" class="btn btn-primary ml-auto">Tambah Tipe Pembayaran</a>
        </div>

        {{-- Tabel Data --}}
        <div class="overflow-x-auto rounded-box bg-white p-5 shadow-xs">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th class="w-full">Nama Tipe Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paymentTypes as $index => $type)
                    <tr>
                        <th>{{ $index + 1 }}</th>
                        <td class="font-bold">{{ $type->name }}</td>
                        <td class="flex">
                            {{-- Tombol Edit --}}
                            <a href="{{ route('admin.payment-types.edit', $type->id) }}" class="btn btn-sm btn-primary mr-2">Edit</a>

                            {{-- Tombol Hapus --}}
                            <button class="btn btn-sm bg-red-500 text-white" onclick="openDeleteModal(this)" data-id="{{ $type->id }}">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray-500 py-4">Tidak ada tipe pembayaran tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete Modal --}}
    <dialog id="delete_modal" class="modal">
        <form method="POST" class="modal-box">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-bold mb-4">Hapus Tipe Pembayaran</h3>
            <p>Apakah Anda yakin ingin menghapus tipe pembayaran ini?</p>

            <div class="modal-action">
                <button class="btn btn-primary bg-red-600 border-none hover:bg-red-700" type="submit">Hapus</button>
                <button class="btn" onclick="delete_modal.close()" type="button">Batal</button>
            </div>
        </form>
    </dialog>

    <script>
        function openDeleteModal(button) {
            const id = button.dataset.id;
            const form = document.querySelector('#delete_modal form');

            form.action = `/admin/payment-types/${id}`;

            delete_modal.showModal();
        }
    </script>

</x-layouts.admin>
