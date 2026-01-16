<x-layouts.app>
  <section class="max-w-7xl mx-auto py-12 px-6">
    <nav class="mb-6">
      <div class="breadcrumbs">
        <ul>
          <li><a href="{{ route('home') }}" class="link link-neutral">Beranda</a></li>
          <li><a href="#" class="link link-neutral">Event</a></li>
          <li>{{ $event->judul }}</li>
        </ul>
      </div>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
          <figure>
            @php
                $gambar = $event->gambar;
                // Gambar default jika kosong
                $src = 'https://img.daisyui.com/images/stock/photo-1606107557195-0e29a4b5b4aa.webp';

                if (!empty($gambar)) {
                    // Cek apakah link internet (http/https)
                    if (str_starts_with($gambar, 'http')) {
                        $src = $gambar;
                    }
                    // Jika tidak, anggap file lokal di storage
                    else {
                        $src = asset('storage/' . $gambar);
                    }
                }
            @endphp

            <img src="{{ $src }}"
                 alt="{{ $event->judul }}"
                 class="w-full h-96 object-cover" />
          </figure>

          <div class="card-body">
            <div class="flex justify-between items-start gap-4">
              <div>
                <h1 class="text-3xl font-extrabold">{{ $event->judul }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                  {{ \Carbon\Carbon::parse($event->tanggal_waktu)->locale('id')->translatedFormat('d F Y, H:i') }} • 📍
                  {{ $event->lokasi }}
                </p>

                <div class="mt-3 flex gap-2 items-center">
                  <span class="badge badge-primary">{{ $event->kategori?->nama ?? 'Tanpa Kategori' }}</span>
                  <span class="badge">{{ $event->user?->name ?? 'Penyelenggara' }}</span>
                </div>
              </div>
            </div>

            <p class="mt-4 text-gray-700 leading-relaxed">{{ $event->deskripsi }}</p>

            <div class="divider"></div>

            <h3 class="text-xl font-bold">Pilih Tiket</h3>

            <div class="mt-4 space-y-4">
              @forelse($event->tikets as $tiket)
              <div class="card card-side shadow-sm p-4 items-center border border-gray-100">
                <div class="flex-1">
                  <h4 class="font-bold text-lg">{{ $tiket->tipe }}</h4>
                  <p class="text-sm text-gray-500">Stok: <span id="stock-{{ $tiket->id }}">{{ $tiket->stok }}</span></p>
                  <p class="text-sm mt-1 text-gray-600">{{ $tiket->keterangan ?? '' }}</p>
                </div>

                <div class="w-44 text-right">
                  <div class="text-lg font-bold text-blue-900">
                    {{ $tiket->harga ? 'Rp ' . number_format($tiket->harga, 0, ',', '.') : 'Gratis' }}
                  </div>

                  <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-sm btn-square btn-outline" data-action="dec" data-id="{{ $tiket->id }}" aria-label="Kurangi">
                      −
                    </button>
                    <input id="qty-{{ $tiket->id }}" type="number" min="0" max="{{ $tiket->stok }}" value="0"
                      class="input input-bordered input-sm w-16 text-center font-bold" data-id="{{ $tiket->id }}" readonly />
                    <button type="button" class="btn btn-sm btn-square btn-outline" data-action="inc" data-id="{{ $tiket->id }}" aria-label="Tambah">
                      +
                    </button>
                  </div>

                  <div class="text-xs text-gray-500 mt-2">Subtotal: <span id="subtotal-{{ $tiket->id }}" class="font-medium">Rp 0</span></div>
                </div>
              </div>
              @empty
              <div class="alert alert-info">Tiket belum tersedia untuk acara ini.</div>
              @endforelse
            </div>

          </div>
        </div>
      </div>

      <aside class="lg:col-span-1">
        <div class="card sticky top-24 p-6 bg-base-100 shadow h-fit">
          <h4 class="font-bold text-lg mb-4">Ringkasan Pembelian</h4>

          <div class="space-y-2">
            <div class="flex justify-between text-sm text-gray-500">
                <span>Total Item</span>
                <span id="summaryItems" class="font-medium">0</span>
            </div>
            <div class="divider my-2"></div>
            <div class="flex justify-between text-xl font-bold text-blue-900">
                <span>Total Bayar</span>
                <span id="summaryTotal">Rp 0</span>
            </div>
          </div>

          <div class="mt-6 bg-gray-50 p-3 rounded-lg">
            <h5 class="text-xs font-bold text-gray-400 uppercase mb-2">Tiket Dipilih:</h5>
            <div id="selectedList" class="space-y-2 text-sm text-gray-700 max-h-40 overflow-y-auto">
                <p class="text-gray-400 text-xs italic">Belum ada tiket dipilih</p>
            </div>
          </div>

          @auth
            <button id="checkoutButton" class="btn btn-primary !bg-blue-900 text-white btn-block mt-6 shadow-lg" onclick="openCheckout()" disabled>
                Checkout Sekarang
            </button>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary btn-block mt-6 text-white">
                Login untuk Checkout
            </a>
          @endauth

        </div>
      </aside>
    </div>

    <dialog id="checkout_modal" class="modal modal-bottom sm:modal-middle">
      <div class="modal-box">
        <h3 class="font-bold text-lg border-b pb-2">Konfirmasi Pembelian</h3>

        <div class="py-4 space-y-3">
          <div id="modalItems" class="space-y-2 max-h-60 overflow-y-auto">
            <p class="text-gray-500 text-center">Memuat item...</p>
          </div>

          <div class="divider"></div>

          <div class="flex justify-between items-center text-lg">
            <span class="font-bold text-gray-600">Total Tagihan</span>
            <span class="font-extrabold text-blue-900 text-2xl" id="modalTotal">Rp 0</span>
          </div>
        </div>

        <div class="modal-action">
          <form method="dialog">
            <button class="btn btn-ghost">Batal</button>
          </form>
          <button type="button" class="btn btn-primary !bg-blue-900 text-white px-8" id="confirmCheckout">
            Konfirmasi
          </button>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button>close</button>
      </form>
    </dialog>

  </section>

  <script>
    (function () {
      // --- Helper Format Rupiah ---
      const formatRupiah = (value) => {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
      }

      // --- Data Tiket dari PHP ke JS ---
      const tickets = {
        @foreach($event->tikets as $tiket)
          {{ $tiket->id }}: {
            id: {{ $tiket->id }},
            price: {{ $tiket->harga ?? 0 }},
            stock: {{ $tiket->stok }},
            tipe: "{{ e($tiket->tipe) }}"
          },
        @endforeach
      };

      // --- Referensi Element DOM ---
      const summaryItemsEl = document.getElementById('summaryItems');
      const summaryTotalEl = document.getElementById('summaryTotal');
      const selectedListEl = document.getElementById('selectedList');
      const checkoutButton = document.getElementById('checkoutButton');

      // --- Fungsi Update Ringkasan (Sidebar) ---
      function updateSummary() {
        let totalQty = 0;
        let totalPrice = 0;
        let selectedHtml = '';

        Object.values(tickets).forEach(t => {
          const qtyInput = document.getElementById('qty-' + t.id);
          if (!qtyInput) return;

          const qty = Number(qtyInput.value || 0);
          if (qty > 0) {
            totalQty += qty;
            totalPrice += qty * t.price;
            selectedHtml += `
                <div class="flex justify-between items-center border-b border-gray-100 pb-1 last:border-0">
                    <span class="font-medium">${t.tipe} <span class="text-xs text-gray-500">x${qty}</span></span>
                    <span class="font-bold">${formatRupiah(qty * t.price)}</span>
                </div>`;
          }
        });

        summaryItemsEl.textContent = totalQty;
        summaryTotalEl.textContent = formatRupiah(totalPrice);
        selectedListEl.innerHTML = selectedHtml || '<p class="text-gray-400 text-xs italic">Belum ada tiket dipilih</p>';

        if(checkoutButton) {
            checkoutButton.disabled = totalQty === 0;
            if(totalQty > 0) {
                checkoutButton.classList.remove('btn-disabled');
            }
        }
      }

      // --- Fungsi Update Subtotal per Baris Tiket ---
      function updateTicketSubtotal(id) {
        const t = tickets[id];
        const qty = Number(document.getElementById('qty-' + id).value || 0);
        const subtotalEl = document.getElementById('subtotal-' + id);
        if (subtotalEl) subtotalEl.textContent = formatRupiah(qty * t.price);
      }

      // --- Event Listener Tombol Tambah (+) ---
      document.querySelectorAll('[data-action="inc"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.currentTarget.dataset.id;
          const input = document.getElementById('qty-' + id)
          const info = tickets[id];
          if (!input || !info) return;

          let val = Number(input.value || 0);
          if (val < info.stock) {
              val++;
              input.value = val;
              updateTicketSubtotal(id);
              updateSummary();
          } else {
              alert('Stok maksimal tercapai!');
          }
        });
      });

      // --- Event Listener Tombol Kurang (-) ---
      document.querySelectorAll('[data-action="dec"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.currentTarget.dataset.id;
          const input = document.getElementById('qty-' + id);
          if (!input) return;

          let val = Number(input.value || 0);
          if (val > 0) {
              val--;
              input.value = val;
              updateTicketSubtotal(id);
              updateSummary();
          }
        });
      });

      // --- Fungsi Buka Modal (Global) ---
      window.openCheckout = function () {
        const modal = document.getElementById('checkout_modal');
        const modalItems = document.getElementById('modalItems');
        const modalTotal = document.getElementById('modalTotal');

        let itemsHtml = '';
        let total = 0;

        // Loop tiket untuk ditampilkan di modal
        Object.values(tickets).forEach(t => {
          const qty = Number(document.getElementById('qty-' + t.id).value || 0);
          if (qty > 0) {
            const subtotal = qty * t.price;
            total += subtotal;
            itemsHtml += `
                <div class="flex justify-between items-center bg-gray-50 p-3 rounded mb-2">
                    <div>
                        <div class="font-bold text-sm">${t.tipe}</div>
                        <div class="text-xs text-gray-500">${formatRupiah(t.price)} x ${qty}</div>
                    </div>
                    <div class="font-bold text-blue-900">${formatRupiah(subtotal)}</div>
                </div>`;
          }
        });

        modalItems.innerHTML = itemsHtml || '<p class="text-gray-500 text-center">Belum ada item.</p>';
        modalTotal.textContent = formatRupiah(total);

        // Tampilkan Modal
        if (typeof modal.showModal === 'function') {
          modal.showModal();
        } else {
          modal.classList.add('modal-open'); // Fallback browser lama
        }
      }

      // --- INIT ---
      updateSummary();

      // ==========================================
      // BAGIAN PENTING: LOGIKA TOMBOL KONFIRMASI
      // (Sekarang berada di dalam scope 'tickets')
      // ==========================================
      const confirmBtn = document.getElementById('confirmCheckout');

      if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
          const btn = confirmBtn;

          // 1. Ubah status tombol loading
          const originalText = btn.textContent;
          btn.setAttribute('disabled', 'disabled');
          btn.innerHTML = '<span class="loading loading-spinner"></span> Memproses...';

          // 2. Kumpulkan data tiket
          const items = [];
          Object.values(tickets).forEach(t => {
            const qty = Number(document.getElementById('qty-' + t.id).value || 0);
            if (qty > 0) items.push({ tiket_id: t.id, jumlah: qty });
          });

          // 3. Validasi Client Side
          if (items.length === 0) {
            alert('Tidak ada tiket dipilih');
            btn.removeAttribute('disabled');
            btn.textContent = originalText;
            return;
          }

          // 4. Kirim ke Server (Fetch)
          try {
            // Ambil CSRF Token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) throw new Error('CSRF Token tidak ditemukan di layout!');

            const res = await fetch("{{ route('orders.store') }}", {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
              },
              body: JSON.stringify({ event_id: {{ $event->id }}, items })
            });

            // 5. Cek Response
            if (!res.ok) {
              const text = await res.text();
              try {
                  const jsonErr = JSON.parse(text);
                  throw new Error(jsonErr.message || 'Gagal membuat pesanan');
              } catch(e) {
                  // Jika error bukan JSON, lempar error text mentah
                  if(e.message !== 'Gagal membuat pesanan') throw e;
                  throw new Error(text || 'Gagal membuat pesanan');
              }
            }

            // 6. Sukses -> Redirect
            const data = await res.json();
            window.location.href = data.redirect || '{{ route('orders.index') }}';

          } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan: ' + err.message);
            // Reset tombol jika gagal
            btn.removeAttribute('disabled');
            btn.textContent = originalText;
          }
        });
      }

    }) ();
  </script>
</x-layouts.app>
