(function () {
    'use strict';

    // ---------- Sidebar / section navigation ----------
    var sidebar = document.getElementById('dashSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggleBtn = document.getElementById('sidebarToggle');
    var navItems = document.querySelectorAll('.nav-item');
    var contentPages = document.querySelectorAll('.content-page');
    var adminNavBtns = document.querySelectorAll('.admin-nav-btn');
    var todayDateEl = document.getElementById('todayDate');
    var isSidebarOpen = true;

    // ---------- Search global yang peka halaman aktif ----------
    var searchInput = document.getElementById('adminSearch');
    var searchWrap = document.getElementById('adminSearchWrap');
    var searchTimer = null;
    var currentSection = 'dashboard';

    (function () {
        var s = new URLSearchParams(window.location.search).get('section');
        if (['dashboard', 'karyawan', 'penggajian', 'riwayat', 'laporan', 'profil', 'pengaturan'].indexOf(s) > -1) {
            currentSection = s;
        }
    })();

    var qTerakhirDiterapkan = (new URL(window.location.href)).searchParams.get('q') || '';

    var searchPlaceholders = {
        dashboard: 'Cari di dashboard ini...',
        karyawan: 'Cari karyawan...',
        penggajian: 'Cari karyawan, periode, atau status...',
        riwayat: 'Cari karyawan pada periode ini...',
        laporan: 'Cari laporan atau periode...'
    };

    function aturSearchBar(section) {
        currentSection = section;
        if (!searchInput) return;
        if (section === 'profil' || section === 'pengaturan') {
            if (searchWrap) searchWrap.classList.add('hidden');
            return;
        }
        if (searchWrap) searchWrap.classList.remove('hidden');
        searchInput.placeholder = searchPlaceholders[section] || 'Cari...';
    }

    function setSidebar(open) {
        isSidebarOpen = open;
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay.classList.toggle('hidden', !open);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            setSidebar(!isSidebarOpen);
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            setSidebar(true);
        });
    }

    function showSection(section) {
        navItems.forEach(function (btn) {
            var active = btn.dataset.section === section;
            btn.classList.toggle('bg-blue-600', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('shadow-lg', active);
            btn.classList.toggle('shadow-blue-200', active);
            btn.classList.toggle('text-slate-600', !active);
            btn.classList.toggle('hover:bg-blue-50', !active);
            btn.classList.toggle('hover:text-blue-600', !active);

            var icon = btn.querySelector('svg');
            if (icon) {
                icon.classList.toggle('text-white', active);
                icon.classList.toggle('text-slate-400', !active);
                icon.classList.toggle('group-hover:text-blue-600', !active);
            }
        });

        contentPages.forEach(function (el) {
            var isActive = el.id === 'content-' + section;
            if (isActive) {
                el.classList.remove('hidden', 'animate-in', 'fade-in');
                void el.offsetWidth;
                el.classList.add('animate-in', 'fade-in');
            } else {
                el.classList.add('hidden');
            }
        });

        aturSearchBar(section);

        if (section !== 'profil' && section !== 'pengaturan' && searchInput) {
            var nilaiSearch = (searchInput.value || '').trim();
            if (nilaiSearch !== qTerakhirDiterapkan) {
                jalankanSearchGlobal();
            }
        }
    }

    navItems.forEach(function (btn) {
        btn.addEventListener('click', function () {
            showSection(btn.dataset.section);
        });
    });

    showSection(currentSection);

    adminNavBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            showSection('karyawan');
        });
    });

    document.addEventListener('click', function (e) {
        var target = e.target.closest('[data-nav-to]');
        if (target) {
            showSection(target.dataset.navTo);
        }
    });

    if (todayDateEl) {
        todayDateEl.textContent = new Date().toLocaleDateString('id-ID', { dateStyle: 'long' });
    }

    // ---------- Notifikasi: dropdown ----------
    var btnNotifikasi = document.getElementById('notifikasiBtn');
    var dropdownNotifikasi = document.getElementById('notifikasiDropdown');

    function toggleNotifikasi(tampil) {
        if (!dropdownNotifikasi) return;
        var akanTampil = typeof tampil === 'boolean' ? tampil : dropdownNotifikasi.classList.contains('hidden');
        if (akanTampil) {
            dropdownNotifikasi.classList.remove('hidden', 'animate-in', 'fade-in');
            void dropdownNotifikasi.offsetWidth;
            dropdownNotifikasi.classList.add('animate-in', 'fade-in');
            if (btnNotifikasi) btnNotifikasi.setAttribute('aria-expanded', 'true');
        } else {
            dropdownNotifikasi.classList.add('hidden');
            if (btnNotifikasi) btnNotifikasi.setAttribute('aria-expanded', 'false');
        }
    }

    if (btnNotifikasi) {
        btnNotifikasi.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleNotifikasi();
        });
    }

    document.addEventListener('click', function (e) {
        if (!dropdownNotifikasi || dropdownNotifikasi.classList.contains('hidden')) return;
        if (e.target.closest('#notifikasiDropdown') || e.target.closest('#notifikasiBtn')) return;
        toggleNotifikasi(false);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && dropdownNotifikasi && !dropdownNotifikasi.classList.contains('hidden')) {
            toggleNotifikasi(false);
        }
    });

    function perbaruiBadgeNotifikasi(sisaUnread) {
        var badge = document.getElementById('notifikasiBadge');
        if (sisaUnread > 0) {
            if (badge) {
                badge.textContent = sisaUnread;
                return;
            }
            var btn = document.getElementById('notifikasiBtn');
            if (!btn) return;
            badge = document.createElement('span');
            badge.id = 'notifikasiBadge';
            badge.className = 'absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-white';
            badge.textContent = sisaUnread;
            btn.appendChild(badge);
        } else if (badge) {
            badge.remove();
        }
    }

    document.addEventListener('click', function (e) {
        var item = e.target.closest('.notif-item');
        if (!item || item.dataset.unread !== '1' || !item.dataset.id) return;

        item.dataset.unread = '0';
        item.classList.remove('bg-blue-50/60');
        var chip = item.querySelector('.notif-chip-baru');
        if (chip) chip.remove();
        var status = item.querySelector('.notif-status');
        if (status) status.classList.remove('hidden');
        var hapus = item.parentElement ? item.parentElement.querySelector('.hapus-btn') : null;
        if (hapus) hapus.classList.remove('hidden');

        fetch('/admin/notifikasi/tandai-baca', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ id: item.dataset.id })
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return { ok: false };
                });
            })
            .then(function (data) {
                if (data.ok && typeof data.unread === 'number') {
                    perbaruiBadgeNotifikasi(data.unread);
                }
            });
    });

    document.addEventListener('click', function (e) {
        var hapus = e.target.closest('.hapus-btn');
        if (!hapus || !hapus.dataset.id) return;
        e.preventDefault();
        e.stopPropagation();
        if (!window.confirm('Hapus notifikasi ini?')) return;

        fetch('/admin/notifikasi/' + hapus.dataset.id + '/hapus', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: '{}'
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return { ok: false };
                });
            })
            .then(function (data) {
                if (!data.ok) return;
                var wrap = hapus.closest('.relative') || hapus.parentElement;
                if (wrap) wrap.remove();
                if (typeof data.unread === 'number') {
                    perbaruiBadgeNotifikasi(data.unread);
                }
            });
    });

    var btnTandaiSemua = document.getElementById('tandaiSemuaBtn');

    function ubahSemuaItemJadiBaca() {
        var items = document.querySelectorAll('.notif-item[data-unread="1"]');
        for (var i = 0; i < items.length; i++) {
            items[i].dataset.unread = '0';
            items[i].classList.remove('bg-blue-50/60');
            var chip = items[i].querySelector('.notif-chip-baru');
            if (chip) chip.remove();
            var status = items[i].querySelector('.notif-status');
            if (status) status.classList.remove('hidden');
            var hapus = items[i].parentElement ? items[i].parentElement.querySelector('.hapus-btn') : null;
            if (hapus) hapus.classList.remove('hidden');
        }
    }

    if (btnTandaiSemua) {
        btnTandaiSemua.addEventListener('click', function (e) {
            e.stopPropagation();
            ubahSemuaItemJadiBaca();
            perbaruiBadgeNotifikasi(0);
            btnTandaiSemua.style.display = 'none';

            fetch('/admin/notifikasi/tandai-baca', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: '{}'
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { ok: false };
                    });
                })
                .then(function (data) {
                    if (data.ok && typeof data.unread === 'number') {
                        perbaruiBadgeNotifikasi(data.unread);
                    }
                });
        });
    }

    // ---------- Karyawan: table, filter, pagination ----------
    var wrap = document.getElementById('karyawan-table-wrap');

    // data-url dari route() bisa berupa URL absolut domain APP_URL (mis. saat
    // APP_URL menunjuk domain produksi). Ubah jadi path relatif host agar
    // selalu mengarah ke host yang sedang diakses (hindari fetch gagal).
    function toRelativeUrl(url) {
        if (!url) return url;
        try {
            var parsed = new URL(url, window.location.origin);
            if (parsed.origin === window.location.origin) {
                return parsed.pathname + parsed.search + parsed.hash;
            }
            return parsed.pathname + parsed.search + parsed.hash;
        } catch (e) {
            return url;
        }
    }

    var baseKaryawanUrl = wrap && wrap.dataset.url ? toRelativeUrl(wrap.dataset.url) : '/admin/karyawan';
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function buildKaryawanUrl() {
        var params = new URLSearchParams();
        if (searchInput && searchInput.value.trim() !== '') {
            params.set('q', searchInput.value.trim());
        }
        var filter = wrap ? wrap.querySelector('#departemenFilter') : null;
        if (filter && filter.value) {
            params.set('departemen', filter.value);
        }
        var jabatanFilter = wrap ? wrap.querySelector('#jabatanFilter') : null;
        if (jabatanFilter && jabatanFilter.value) {
            params.set('jabatan', jabatanFilter.value);
        }
        var statusPeranFilter = wrap ? wrap.querySelector('#statusPeranFilter') : null;
        if (statusPeranFilter && statusPeranFilter.value) {
            params.set('status_peran', statusPeranFilter.value);
        }
        var qs = params.toString();
        return baseKaryawanUrl + (qs !== '' ? '?' + qs : '');
    }

    function refreshKaryawanTable() {
        loadKaryawan(buildKaryawanUrl());
    }

    function loadKaryawan(url) {
        if (!url) return;
        fetch(url, {
            headers: { 'Accept': 'text/html' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Gagal memuat data karyawan.');
                return res.text();
            })
            .then(function (html) {
                if (typeof tutupAksiMenu === 'function') tutupAksiMenu();
                if (wrap) wrap.innerHTML = html;
            })
            .catch(function (err) {
                console.error(err);
            });
    }

    var tokenPencarian = 0;
    var pencarianSpinner = null;

    function dataUrlSaatIni(nilai) {
        var u = new URL(window.location.pathname, window.location.origin);
        var cu = new URL(window.location.href);
        ['departemen', 'jabatan', 'bulan', 'tahun'].forEach(function (k) {
            var v = cu.searchParams.get(k);
            if (v) u.searchParams.set(k, v);
        });
        if (nilai !== '') u.searchParams.set('q', nilai);
        u.searchParams.set('section', currentSection);
        return u.toString();
    }

    function gantiQUrl(nilai) {
        try {
            var cu = new URL(window.location.href);
            if (nilai === '') {
                cu.searchParams.delete('q');
            } else {
                cu.searchParams.set('q', nilai);
            }
            cu.searchParams.set('section', currentSection);
            history.replaceState(null, '', cu.toString());
        } catch (e) {}
    }

    function tampilkanLoading(aktif) {
        if (!pencarianSpinner) {
            pencarianSpinner = document.createElement('div');
            pencarianSpinner.id = 'cariLoading';
            pencarianSpinner.className = 'hidden fixed top-20 left-1/2 -translate-x-1/2 z-[80]';
            pencarianSpinner.innerHTML = '<div class="flex items-center gap-2 bg-white rounded-full px-4 py-2 shadow-lg border border-slate-100">' +
                '<span class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>' +
                '<span class="text-sm font-medium text-slate-600">Mencari...</span></div>';
            document.body.appendChild(pencarianSpinner);
        }
        pencarianSpinner.classList.toggle('hidden', !aktif);
        var aktifPage = document.getElementById('content-' + currentSection);
        if (aktifPage) aktifPage.classList.toggle('opacity-50', aktif);
    }

    function terapkanHasil(html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');

        var skrip = doc.querySelectorAll('script');
        for (var i = 0; i < skrip.length; i++) {
            var teks = skrip[i].textContent || '';
            var penanda = 'window.PENGGAJIAN_DETAILS = ';
            var idx = teks.indexOf(penanda);
            if (idx > -1) {
                var json = teks.substring(idx + penanda.length).replace(/;?\s*$/, '').trim();
                try {
                    window.PENGGAJIAN_DETAILS = JSON.parse(json);
                } catch (e) {}
            }
        }

        function salin(id) {
            var tgt = document.getElementById(id);
            var src = doc.getElementById(id);
            if (tgt && src) tgt.innerHTML = src.innerHTML;
        }

        salin('dash-karyawan');
        salin('dash-aktivitas');
        salin('dash-penggajian');

        if (typeof tutupAksiMenu === 'function') tutupAksiMenu();
        var wrapTarget = document.getElementById('karyawan-table-wrap');
        var wrapSumber = doc.getElementById('karyawan-table-wrap');
        if (wrapTarget && wrapSumber) wrapTarget.innerHTML = wrapSumber.innerHTML;

        salin('penggajian-tbody');
        salin('penggajian-ringkasan');
        salin('riwayat-konten');
        salin('laporan-terbaru');

        if (pilihSemuaBayar) pilihSemuaBayar.checked = false;
        muatInfoPilihan();
    }

    function jalankanSearchGlobal() {
        if (!searchInput || currentSection === 'profil') return;
        var nilai = searchInput.value.trim();
        if (nilai === qTerakhirDiterapkan) return;
        qTerakhirDiterapkan = nilai;

        var t = ++tokenPencarian;
        gantiQUrl(nilai);
        tampilkanLoading(true);

        fetch(dataUrlSaatIni(nilai), {
            headers: { 'Accept': 'text/html', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(function (res) {
                if (!res.ok) throw new Error(String(res.status));
                return res.text();
            })
            .then(function (html) {
                if (t !== tokenPencarian) return;
                terapkanHasil(html);
                tampilkanLoading(false);
            })
            .catch(function () {
                if (t !== tokenPencarian) return;
                tampilkanLoading(false);
            });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            if (currentSection === 'profil') return;
            searchTimer = setTimeout(jalankanSearchGlobal, 350);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimer);
                jalankanSearchGlobal();
            }
        });
    }

    if (wrap) {
        wrap.addEventListener('change', function (e) {
            if (e.target && (e.target.id === 'departemenFilter' || e.target.id === 'jabatanFilter' || e.target.id === 'statusPeranFilter')) {
                refreshKaryawanTable();
            }
        });

        wrap.addEventListener('click', function (e) {
            var pageBtn = e.target.closest('.btn-page-karyawan');
            if (pageBtn && pageBtn.dataset.url && !pageBtn.disabled) {
                e.preventDefault();
                loadKaryawan(pageBtn.dataset.url);
            }
        });
    }

    // ---------- Karyawan: modal tambah / edit ----------
    var modal = document.getElementById('karyawanModal');
    var form = document.getElementById('karyawanForm');
    var formTitle = document.getElementById('karyawanModalTitle');
    var formMethod = document.getElementById('karyawanFormMethod');
    var formError = document.getElementById('karyawanFormError');
    var formErrorText = document.getElementById('karyawanFormErrorText');
    var addBtn = document.getElementById('btnTambahKaryawan');
    var editingId = null;

    function field(name) {
        return form ? form.querySelector('[name="' + name + '"]') : null;
    }

    function setSelectValue(select, value) {
        if (!select || !value) return;
        var escaped = value.replace(/"/g, '\\"');
        if (select.querySelector('option[value="' + escaped + '"]') === null) {
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = value;
            select.appendChild(opt);
        }
        select.value = value;
    }

    var karyawanModalTrigger = null;

    function formatRupiah(digits) {
        return String(digits).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function setGajiPokok(raw) {
        var hidden = field('gaji_pokok');
        var disp = document.getElementById('gaji_pokok_display');
        var digits = String(raw == null ? '' : raw).replace(/[^\d]/g, '');
        if (hidden) hidden.value = digits;
        if (disp) disp.value = digits === '' ? '' : formatRupiah(digits);
    }

    function tampilkanTabKaryawan(nama) {
        if (!modal) return;
        var tabs = modal.querySelectorAll('.karyawan-tab');
        for (var i = 0; i < tabs.length; i++) {
            var aktif = tabs[i].dataset.tab === nama;
            tabs[i].setAttribute('aria-selected', aktif ? 'true' : 'false');
            tabs[i].classList.toggle('bg-white', aktif);
            tabs[i].classList.toggle('text-blue-600', aktif);
            tabs[i].classList.toggle('shadow-sm', aktif);
            tabs[i].classList.toggle('text-slate-600', !aktif);
            tabs[i].classList.toggle('hover:text-slate-800', !aktif);
        }
        var panels = modal.querySelectorAll('.karyawan-tab-panel');
        for (var j = 0; j < panels.length; j++) {
            panels[j].classList.toggle('hidden', panels[j].dataset.tabPanel !== nama);
        }
    }

    function openKaryawanModal(mode, data) {
        if (!modal || !form) return;
        karyawanModalTrigger = document.activeElement;
        form.reset();
        formError.classList.add('hidden');
        editingId = null;
        tampilkanTabKaryawan('pribadi');

        if (mode === 'edit' && data) {
            formTitle.textContent = 'Edit Karyawan';
            formMethod.value = 'PUT';
            field('id_karyawan').value = data.idKaryawan || '';
            field('nama').value = data.nama || '';
            field('no_hp').value = data.noHp || '';
            field('email').value = data.email || '';
            setSelectValue(field('departemen'), data.departemen || '');
            setSelectValue(field('posisi'), data.posisi || '');
            setGajiPokok(data.gaji);
            field('tanggal_masuk').value = data.tanggal || '';
            field('status').value = data.status || 'Aktif';
            if (field('status_peran')) field('status_peran').value = data.statusPeran || 'Karyawan';
            if (field('periode_gaji')) field('periode_gaji').value = data.periodeGaji || 'Bulanan';
            field('nama_bank').value = data.bank || '';
            field('nomor_rekening').value = data.rekening || '';
            field('nama_pemilik_rekening').value = data.pemilik || '';
            editingId = data.id || null;
        } else {
            formTitle.textContent = 'Tambah Karyawan';
            formMethod.value = '';
            setGajiPokok('');
            field('status').value = 'Aktif';
            if (field('status_peran')) field('status_peran').value = 'Karyawan';
            if (field('periode_gaji')) field('periode_gaji').value = 'Bulanan';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(function () {
            var first = form.querySelector('#id_karyawan');
            if (first) first.focus();
        }, 50);
    }

    function closeKaryawanModal() {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (karyawanModalTrigger && karyawanModalTrigger.isConnected) {
            karyawanModalTrigger.focus();
        }
        karyawanModalTrigger = null;
    }

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            openKaryawanModal('create');
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            var tab = e.target.closest('.karyawan-tab');
            if (tab) {
                tampilkanTabKaryawan(tab.dataset.tab);
                return;
            }
            if (e.target.closest('[data-close-karyawan-modal]')) {
                closeKaryawanModal();
            }
        });

        // Focus trap: pertahankan fokus di dalam modal selama terbuka.
        modal.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') return;
            var candidates = modal.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])');
            var focusables = [];
            for (var i = 0; i < candidates.length; i++) {
                if (candidates[i].offsetParent !== null) focusables.push(candidates[i]);
            }
            if (focusables.length === 0) return;
            var first = focusables[0];
            var last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeKaryawanModal();
            }
        });
    }

    var gajiDisplay = document.getElementById('gaji_pokok_display');
    if (gajiDisplay) {
        gajiDisplay.addEventListener('input', function () {
            var digits = gajiDisplay.value.replace(/[^\d]/g, '');
            gajiDisplay.value = digits === '' ? '' : formatRupiah(digits);
            if (field('gaji_pokok')) field('gaji_pokok').value = digits;
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!modal || modal.classList.contains('hidden')) return;

            var isEdit = formMethod.value === 'PUT';
            var url = isEdit ? baseKaryawanUrl + '/' + editingId : baseKaryawanUrl;
            var fd = new FormData(form);

            formError.classList.add('hidden');

            fetch(url, {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
                .then(function (res) {
                    if (res.ok) {
                        closeKaryawanModal();
                        refreshKaryawanTable();
                        return;
                    }
                    return res.json().catch(function () {
                        return { message: 'Terjadi kesalahan pada server.' };
                    }).then(function (data) {
                        var messages = [];
                        var errors = data.errors || {};
                        Object.keys(errors).forEach(function (key) {
                            (errors[key] || []).forEach(function (msg) {
                                messages.push(msg);
                            });
                        });
                        if (messages.length === 0) {
                            messages.push(data.message || 'Terjadi kesalahan pada server.');
                        }
                        formErrorText.textContent = messages.join(' ');
                        formError.classList.remove('hidden');
                    });
                })
                .catch(function () {
                    formErrorText.textContent = 'Terjadi kesalahan pada server.';
                    formError.classList.remove('hidden');
                });
        });
    }

    // ---------- Karyawan: hapus dengan konfirmasi ----------
    function deleteKaryawan(btn) {
        var nama = btn.dataset.nama || 'Karyawan ini';
        if (!confirm('Yakin ingin menghapus karyawan "' + nama + '"?')) return;

        var fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('_method', 'DELETE');

        fetch(baseKaryawanUrl + '/' + btn.dataset.id, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(function (res) {
                if (res.ok) {
                    refreshKaryawanTable();
                    return;
                }
                return res.json().catch(function () {
                    return { message: 'Gagal menghapus karyawan.' };
                }).then(function (data) {
                    alert(data.message || 'Gagal menghapus karyawan.');
                });
            })
            .catch(function () {
                alert('Gagal menghapus karyawan.');
            });
    }

    // ---------- Karyawan: detail modal ----------
    var detailKaryawanModal = document.getElementById('detailKaryawanModal');

    function openDetailKaryawan(id, nama) {
        if (!detailKaryawanModal) return;

        byId('detailKaryawanNama').textContent = nama || '-';
        byId('detailKaryawanAvatar').textContent = initialsOf(nama);

        fetch(baseKaryawanUrl + '/' + id, {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return { message: 'Terjadi kesalahan pada server.' };
                });
            })
            .then(function (d) {
                byId('detailKaryawanNama').textContent = d.nama || '-';
                byId('detailKaryawanId').textContent = d.id_karyawan || '-';
                byId('detailKaryawanAvatar').textContent = initialsOf(d.nama);
                byId('detailKaryawanEmail').textContent = d.email || '-';
                byId('detailKaryawanNoHp').textContent = d.no_hp || '-';
                byId('detailKaryawanDepartemen').textContent = d.departemen || '-';
                if (byId('detailKaryawanPosisi')) byId('detailKaryawanPosisi').textContent = d.posisi || '-';
                byId('detailKaryawanGaji').textContent = 'Rp ' + (d.gaji_pokok || '0');
                byId('detailKaryawanTanggal').textContent = d.tanggal_masuk || '-';
                byId('detailKaryawanStatus').textContent = d.status || '-';
                byId('detailKaryawanStatusPeran').textContent = d.status_peran || '-';
                byId('detailKaryawanPeriodeGaji').textContent = d.periode_gaji || '-';
                byId('detailKaryawanBank').textContent = d.nama_bank || '-';
                byId('detailKaryawanRekening').textContent = d.nomor_rekening || '-';
                byId('detailKaryawanPemilik').textContent = d.nama_pemilik_rekening || '-';

                detailKaryawanModal.classList.remove('hidden');
                detailKaryawanModal.classList.add('flex');
            })
            .catch(function () {
                alert('Gagal memuat detail karyawan.');
            });
    }

    function closeDetailKaryawan() {
        if (!detailKaryawanModal) return;
        detailKaryawanModal.classList.add('hidden');
        detailKaryawanModal.classList.remove('flex');
    }

    if (detailKaryawanModal) {
        detailKaryawanModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-detail-karyawan-modal]')) {
                closeDetailKaryawan();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !detailKaryawanModal.classList.contains('hidden')) {
                closeDetailKaryawan();
            }
        });
    }

    // ---------- Karyawan: menu aksi tabel (dropdown tiga-titik) ----------
    var aksiMenuAktif = null;
    var aksiMenuBtnAktif = null;

    function tutupAksiMenu() {
        if (!aksiMenuAktif) return;
        var menu = aksiMenuAktif;
        var btn = aksiMenuBtnAktif;
        menu.classList.add('hidden');
        menu.style.position = '';
        menu.style.top = '';
        menu.style.left = '';
        if (btn) btn.setAttribute('aria-expanded', 'false');
        // Kembalikan menu ke baris asalnya agar tetap hidup saat dibuka ulang.
        if (btn && btn.isConnected && btn.parentElement) {
            btn.parentElement.appendChild(menu);
        } else {
            menu.remove();
        }
        aksiMenuAktif = null;
        aksiMenuBtnAktif = null;
    }

    function bukaAksiMenu(btn) {
        var menu = btn.parentElement ? btn.parentElement.querySelector('.aksi-menu') : null;
        if (!menu) return;
        tutupAksiMenu();
        // Dipindah ke body agar tidak terpotong overflow tabel / transform section.
        document.body.appendChild(menu);
        menu.classList.remove('hidden');
        menu.style.position = 'fixed';
        var rect = btn.getBoundingClientRect();
        var menuW = menu.offsetWidth;
        var menuH = menu.offsetHeight;
        var left = rect.right - menuW;
        var maxLeft = window.innerWidth - menuW - 8;
        if (left > maxLeft) left = maxLeft;
        if (left < 8) left = 8;
        var top = rect.bottom + 4;
        if (top + menuH > window.innerHeight - 8) {
            top = rect.top - menuH - 4;
            if (top < 8) top = 8;
        }
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        btn.setAttribute('aria-expanded', 'true');
        aksiMenuAktif = menu;
        aksiMenuBtnAktif = btn;
    }

    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('.btn-aksi-menu');
        if (toggle) {
            var sudahTerbuka = (toggle === aksiMenuBtnAktif) && aksiMenuAktif && !aksiMenuAktif.classList.contains('hidden');
            tutupAksiMenu();
            if (!sudahTerbuka) bukaAksiMenu(toggle);
            return;
        }

        var detailBtn = e.target.closest('.btn-detail-karyawan');
        if (detailBtn && detailBtn.dataset.id) {
            tutupAksiMenu();
            openDetailKaryawan(detailBtn.dataset.id, detailBtn.dataset.nama);
            return;
        }

        var editBtn = e.target.closest('.btn-edit-karyawan');
        if (editBtn) {
            tutupAksiMenu();
            openKaryawanModal('edit', editBtn.dataset);
            return;
        }

        var deleteBtn = e.target.closest('.btn-delete-karyawan');
        if (deleteBtn) {
            tutupAksiMenu();
            deleteKaryawan(deleteBtn);
            return;
        }

        if (aksiMenuAktif && !e.target.closest('.aksi-menu')) {
            tutupAksiMenu();
        }
    });

    window.addEventListener('scroll', function () {
        if (aksiMenuAktif) tutupAksiMenu();
    }, true);

    window.addEventListener('resize', function () {
        if (aksiMenuAktif) tutupAksiMenu();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && aksiMenuAktif) tutupAksiMenu();
    });

    // ---------- Header: dropdown profil ----------
    var profilMenuBtn = document.getElementById('profilMenuBtn');
    var profilMenuDropdown = document.getElementById('profilMenuDropdown');

    function tutupProfilMenu() {
        if (!profilMenuDropdown) return;
        profilMenuDropdown.classList.add('hidden');
        if (profilMenuBtn) profilMenuBtn.setAttribute('aria-expanded', 'false');
    }

    if (profilMenuBtn && profilMenuDropdown) {
        profilMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (profilMenuDropdown.classList.contains('hidden')) {
                profilMenuDropdown.classList.remove('hidden');
                profilMenuBtn.setAttribute('aria-expanded', 'true');
            } else {
                tutupProfilMenu();
            }
        });

        var profilMenuEdit = document.getElementById('profilMenuEdit');
        if (profilMenuEdit) {
            profilMenuEdit.addEventListener('click', function () {
                tutupProfilMenu();
                showSection('profil');
                openProfilModal();
            });
        }

        document.addEventListener('click', function (e) {
            if (profilMenuDropdown.classList.contains('hidden')) return;
            if (e.target.closest('#profilMenuDropdown') || e.target.closest('#profilMenuBtn')) return;
            tutupProfilMenu();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !profilMenuDropdown.classList.contains('hidden')) tutupProfilMenu();
        });
    }

    // ---------- Pengaturan: logo perusahaan ----------
    (function () {
        var form = document.getElementById('logoForm');
        if (!form) return;

        var input = document.getElementById('logoInput');
        var preview = document.getElementById('logoPreview');
        var fileName = document.getElementById('logoFileName');
        var errorBox = document.getElementById('logoFormError');
        var successBox = document.getElementById('logoFormSuccess');
        var btnSimpan = document.getElementById('btnSimpanLogo');
        var btnReset = document.getElementById('btnResetLogo');

        function resetPesan() {
            if (errorBox) { errorBox.classList.add('hidden'); errorBox.textContent = ''; }
            if (successBox) { successBox.classList.add('hidden'); successBox.textContent = ''; }
        }

        function tampilkanPesan(el, teks) {
            resetPesan();
            if (!el) return;
            el.textContent = teks;
            el.classList.remove('hidden');
        }

        if (input) {
            input.addEventListener('change', function () {
                resetPesan();
                var file = input.files && input.files[0];
                if (!file) return;
                if (fileName) fileName.textContent = 'Dipilih: ' + file.name;
                if (preview && file.type.indexOf('image/') === 0) {
                    var reader = new FileReader();
                    reader.onload = function (ev) { preview.src = ev.target.result; };
                    reader.readAsDataURL(file);
                }
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            resetPesan();

            var file = input && input.files && input.files[0];
            if (!file) {
                tampilkanPesan(errorBox, 'Pilih file logo terlebih dahulu.');
                return;
            }

            var csrf = document.querySelector('meta[name="csrf-token"]');
            var fd = new FormData();
            fd.append('logo', file);

            if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.textContent = 'Menyimpan...'; }

            fetch(form.dataset.url || '/admin/pengaturan/logo', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.content : '',
                    'Accept': 'application/json'
                },
                body: fd
            })
                .then(function (res) { return res.json().then(function (j) { return { ok: res.ok, body: j }; }); })
                .then(function (r) {
                    if (!r.ok) {
                        var pesan = r.body && r.body.errors && r.body.errors.logo
                            ? r.body.errors.logo[0]
                            : (r.body && r.body.message) || 'Gagal menyimpan logo.';
                        tampilkanPesan(errorBox, pesan);
                        return;
                    }
                    if (preview && r.body.logo_url) preview.src = r.body.logo_url + '?t=' + Date.now();
                    if (input) input.value = '';
                    if (fileName) fileName.textContent = 'Logo berhasil diperbarui.';
                    var navLogo = document.getElementById('navCompanyLogo');
                    if (navLogo && r.body.logo_url) navLogo.src = r.body.logo_url + '?t=' + Date.now();
                    tampilkanPesan(successBox, r.body.message || 'Logo perusahaan berhasil diperbarui.');
                })
                .catch(function () { tampilkanPesan(errorBox, 'Terjadi kesalahan saat mengunggah logo.'); })
                .finally(function () {
                    if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan Logo'; }
                });
        });

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                resetPesan();
                var csrf = document.querySelector('meta[name="csrf-token"]');
                btnReset.disabled = true;
                fetch(form.dataset.resetUrl || '/admin/pengaturan/logo/reset', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf ? csrf.content : '',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (j) {
                        if (preview && j.logo_url) preview.src = j.logo_url + '?t=' + Date.now();
                        var navLogo = document.getElementById('navCompanyLogo');
                        if (navLogo && j.logo_url) navLogo.src = j.logo_url + '?t=' + Date.now();
                        if (input) input.value = '';
                        if (fileName) fileName.textContent = 'Logo dikembalikan ke default.';
                        tampilkanPesan(successBox, j.message || 'Logo dikembalikan ke default.');
                    })
                    .catch(function () { tampilkanPesan(errorBox, 'Gagal mengembalikan logo default.'); })
                    .finally(function () { btnReset.disabled = false; });
            });
        }
    })();

    // ---------- Pengaturan: ukuran logo ----------
    (function () {
        var form = document.getElementById('logoSizeForm');
        if (!form) return;

        var range = document.getElementById('logoSizeRange');
        var number = document.getElementById('logoSizeNumber');
        var preview = document.getElementById('logoSizePreview');
        var info = document.getElementById('logoSizeInfo');
        var errorBox = document.getElementById('logoSizeFormError');
        var successBox = document.getElementById('logoSizeFormSuccess');
        var btnSimpan = document.getElementById('btnSimpanUkuranLogo');
        var btnReset = document.getElementById('btnResetUkuranLogo');
        var MIN = 40;
        var MAX = 240;
        var DEFAULT = 100;

        function batasi(nilai) {
            var n = Math.round(Number(nilai));
            if (!isFinite(n)) return DEFAULT;
            return Math.max(MIN, Math.min(MAX, n));
        }

        function resetPesan() {
            if (errorBox) { errorBox.classList.add('hidden'); errorBox.textContent = ''; }
            if (successBox) { successBox.classList.add('hidden'); successBox.textContent = ''; }
        }

        function tampilkanPesan(el, teks) {
            resetPesan();
            if (!el) return;
            el.textContent = teks;
            el.classList.remove('hidden');
        }

        function terapkanPratinjau(nilai, sinkron) {
            var v = batasi(nilai);
            if (preview) {
                preview.style.maxWidth = v + 'px';
                preview.style.maxHeight = v + 'px';
            }
            if (sinkron) {
                if (range) range.value = String(v);
                if (number) number.value = String(v);
            }
            if (info) info.textContent = v + ' px';
        }

        function terapkanUkuranGlobal(nilai) {
            var v = batasi(nilai);
            var navLogo = document.getElementById('navCompanyLogo');
            if (navLogo) {
                navLogo.style.maxWidth = 'min(' + v + 'px, 100%)';
                navLogo.style.maxHeight = v + 'px';
            }
            var slipLogo = document.querySelector('#slipSheetTemplate .slip-logo-img');
            if (slipLogo) {
                slipLogo.style.maxWidth = v + 'px';
                slipLogo.style.maxHeight = v + 'px';
            }
        }

        if (range) {
            range.addEventListener('input', function () {
                resetPesan();
                terapkanPratinjau(this.value, true);
            });
        }

        if (number) {
            number.addEventListener('input', function () {
                resetPesan();
                var v = parseInt(this.value, 10);
                if (!isNaN(v)) terapkanPratinjau(v, false);
            });
            number.addEventListener('change', function () {
                terapkanPratinjau(this.value, true);
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            resetPesan();

            var ukuran = batasi(number ? number.value : (range ? range.value : DEFAULT));
            terapkanPratinjau(ukuran, true);

            var csrf = document.querySelector('meta[name="csrf-token"]');
            var fd = new FormData();
            fd.append('size', String(ukuran));

            if (btnSimpan) { btnSimpan.disabled = true; btnSimpan.textContent = 'Menyimpan...'; }

            fetch(form.dataset.url || '/admin/pengaturan/logo-size', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.content : '',
                    'Accept': 'application/json'
                },
                body: fd
            })
                .then(function (res) { return res.json().then(function (j) { return { ok: res.ok, body: j }; }); })
                .then(function (r) {
                    if (!r.ok) {
                        var pesan = r.body && r.body.errors && r.body.errors.size
                            ? r.body.errors.size[0]
                            : (r.body && r.body.message) || 'Gagal menyimpan ukuran logo.';
                        tampilkanPesan(errorBox, pesan);
                        return;
                    }
                    var v = r.body.size || ukuran;
                    terapkanPratinjau(v, true);
                    terapkanUkuranGlobal(v);
                    tampilkanPesan(successBox, r.body.message || 'Ukuran logo berhasil disimpan.');
                })
                .catch(function () { tampilkanPesan(errorBox, 'Terjadi kesalahan saat menyimpan ukuran logo.'); })
                .finally(function () {
                    if (btnSimpan) { btnSimpan.disabled = false; btnSimpan.textContent = 'Simpan'; }
                });
        });

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                resetPesan();
                var csrf = document.querySelector('meta[name="csrf-token"]');
                btnReset.disabled = true;

                fetch(form.dataset.resetUrl || '/admin/pengaturan/logo-size/reset', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf ? csrf.content : '',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (j) {
                        var v = j.size || DEFAULT;
                        terapkanPratinjau(v, true);
                        terapkanUkuranGlobal(v);
                        tampilkanPesan(successBox, j.message || 'Ukuran logo dikembalikan ke default (100 px).');
                    })
                    .catch(function () { tampilkanPesan(errorBox, 'Gagal mengembalikan ukuran logo default.'); })
                    .finally(function () { btnReset.disabled = false; });
            });
        }
    })();

    // ---------- Pengaturan: Informasi Perusahaan ----------
    (function () {
        var form = document.getElementById('companyInfoForm');
        if (!form) return;

        var btn = document.getElementById('btnSimpanInfo');
        var errorBox = document.getElementById('companyInfoError');
        var successBox = document.getElementById('companyInfoSuccess');

        function resetPesan() {
            if (errorBox) { errorBox.classList.add('hidden'); errorBox.textContent = ''; }
            if (successBox) { successBox.classList.add('hidden'); successBox.textContent = ''; }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            resetPesan();

            var csrf = document.querySelector('meta[name="csrf-token"]');
            var fd = new FormData(form);

            if (btn) { btn.disabled = true; btn.textContent = 'Menyimpan...'; }

            fetch(form.dataset.url || '/admin/pengaturan/info', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf ? csrf.content : '',
                    'Accept': 'application/json'
                },
                body: fd
            })
                .then(function (res) { return res.json().then(function (j) { return { ok: res.ok, body: j }; }); })
                .then(function (r) {
                    if (!r.ok) {
                        var pesan = r.body && r.body.errors
                            ? Object.values(r.body.errors).flat().join(' ')
                            : (r.body && r.body.message) || 'Gagal menyimpan informasi perusahaan.';
                        if (errorBox) { errorBox.textContent = pesan; errorBox.classList.remove('hidden'); }
                        return;
                    }
                    if (successBox) { successBox.textContent = r.body.message || 'Informasi perusahaan berhasil disimpan.'; successBox.classList.remove('hidden'); }
                    setTimeout(function () { if (successBox) successBox.classList.add('hidden'); }, 3000);
                })
                .catch(function () {
                    if (errorBox) { errorBox.textContent = 'Terjadi kesalahan saat menyimpan informasi perusahaan.'; errorBox.classList.remove('hidden'); }
                })
                .finally(function () {
                    if (btn) { btn.disabled = false; btn.textContent = 'Simpan Informasi'; }
                });
        });
    })();

    // ---------- Penggajian: periodo pilihan ----------
    var pilihBulan = document.getElementById('pilihBulan');
    var pilihTahun = document.getElementById('pilihTahun');

    function periodeTerpilih() {
        var sekarang = new Date();
        return {
            bulan: pilihBulan ? pilihBulan.value : String(sekarang.getMonth() + 1),
            tahun: pilihTahun ? pilihTahun.value : String(sekarang.getFullYear())
        };
    }

    function urlDenganPeriode(bulan, tahun) {
        var u = new URL(window.location.pathname, window.location.origin);
        var cu = new URL(window.location.href);
        ['q', 'departemen', 'bulan', 'tahun', 'karyawan'].forEach(function (k) {
            var v = cu.searchParams.get(k);
            if (v) u.searchParams.set(k, v);
        });
        u.searchParams.set('section', currentSection);
        u.searchParams.set('bulan', String(bulan));
        u.searchParams.set('tahun', String(tahun));
        return u.toString();
    }

    function gantiPeriodePenggajian() {
        var p = periodeTerpilih();
        window.location.href = urlDenganPeriode(p.bulan, p.tahun);
    }

    if (pilihBulan) pilihBulan.addEventListener('change', gantiPeriodePenggajian);
    if (pilihTahun) pilihTahun.addEventListener('change', gantiPeriodePenggajian);

    // ---------- Penggajian: proses & bayar semua ----------
    function submitPenggajian(btn, url, successMsg, body, bulan, tahun) {
        if (btn && btn.disabled) return;
        if (btn) {
            btn.disabled = true;
        }
        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: body
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return { message: 'Terjadi kesalahan pada server.' };
                }).then(function (data) {
                    if (res.ok) {
                        alert(data.message || successMsg);
                        window.location.href = urlDenganPeriode(bulan, tahun);
                    } else {
                        alert(data.message || 'Terjadi kesalahan pada server.');
                    }
                });
            })
            .catch(function () {
                alert('Terjadi kesalahan pada server.');
            })
            .finally(function () {
                if (btn) btn.disabled = false;
            });
    }

    var btnProsesGaji = document.getElementById('btnProsesGaji');

    if (btnProsesGaji) {
        btnProsesGaji.addEventListener('click', function () {
            var p = periodeTerpilih();
            var fd = new FormData();
            fd.append('bulan', p.bulan);
            fd.append('tahun', p.tahun);
            submitPenggajian(btnProsesGaji, '/admin/penggajian/proses', 'Penggajian berhasil diproses.', fd, p.bulan, p.tahun);
        });
    }

    // ---------- Penggajian: check pilihan & bayar terpilih / semua ----------
    var konfirmasiBayarModal = document.getElementById('konfirmasiBayarModal');
    var konfirmasiBayarError = document.getElementById('konfirmasiBayarError');
    var konfirmasiBayarErrorText = document.getElementById('konfirmasiBayarErrorText');
    var konfirmasiBayarPeringatan = document.getElementById('konfirmasiBayarPeringatan');
    var konfirmasiBayarPeringatanText = document.getElementById('konfirmasiBayarPeringatanText');
    var konfirmasiBayarListWrap = document.getElementById('konfirmasiBayarListWrap');
    var konfirmasiBayarList = document.getElementById('konfirmasiBayarList');
    var btnKonfirmasiProsesBayar = document.getElementById('btnKonfirmasiProsesBayar');
    var processingBayar = false;
    var modeBayar = null;
    var idBayarTerpilih = [];
    var konfirmasiBayarTotalSemua = '0';

    function cekBayarChecked() {
        var checked = document.querySelectorAll('#penggajian-tbody .cek-bayar:not(:disabled):checked');
        var data = {
            jumlah: 0,
            total: 0,
            jumlahBoleh: 0,
            totalBoleh: 0,
            jumlahDilewati: 0,
            jumlahDilewatiDibayar: 0,
            jumlahDilewatiDraft: 0,
            terpilih: []
        };

        for (var i = 0; i < checked.length; i++) {
            var el = checked[i];
            var boleh = el.dataset.bolehBayar === '1';
            var status = el.dataset.status || '';
            var nominal = Number(el.dataset.total || 0) || 0;

            data.terpilih.push({
                id: el.value,
                nama: el.dataset.nama || '',
                idKaryawan: el.dataset.idKaryawan || '',
                total: nominal,
                bolehBayar: boleh,
                status: status
            });

            data.jumlah++;
            data.total += nominal;

            if (boleh) {
                data.jumlahBoleh++;
                data.totalBoleh += nominal;
            } else {
                data.jumlahDilewati++;
                if (status === 'Sudah Dibayar') {
                    data.jumlahDilewatiDibayar++;
                } else {
                    data.jumlahDilewatiDraft++;
                }
            }
        }

        return data;
    }

    function muatInfoPilihan() {
        var info = byId('penggajianPilihanInfo');
        var btn = byId('btnBayarTerpilih');
        var pilihan = cekBayarChecked();

        if (info) {
            info.textContent = pilihan.jumlah === 0
                ? 'Belum ada karyawan dipilih.'
                : infoPilihanTeks(pilihan);
        }
        if (btn) {
            btn.disabled = pilihan.jumlahBoleh === 0;
        }
    }

    function infoPilihanTeks(pilihan) {
        var teks = pilihan.jumlahBoleh + ' siap dibayar · Rp ' + Math.round(pilihan.totalBoleh).toLocaleString('id-ID');
        if (pilihan.jumlahDilewati > 0) {
            teks += ' · ' + pilihan.jumlahDilewati + ' dilewati';
        }
        return pilihan.jumlahBoleh === 0 && pilihan.jumlah > 0
            ? pilihan.jumlah + ' dipilih tetapi tidak ada yang bisa dibayar.'
            : teks;
    }

    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('cek-bayar')) {
            muatInfoPilihan();
        }
    });

    var pilihSemuaBayar = document.getElementById('pilihSemuaBayar');

    function setSemuaCheck(tercentang) {
        var semua = document.querySelectorAll('#penggajian-tbody .cek-bayar:not(:disabled)');
        for (var i = 0; i < semua.length; i++) {
            semua[i].checked = tercentang;
        }
        muatInfoPilihan();
    }

    if (pilihSemuaBayar) {
        pilihSemuaBayar.addEventListener('change', function () {
            setSemuaCheck(pilihSemuaBayar.checked);
        });
    }

    muatInfoPilihan();

    document.addEventListener('click', function (e) {
        var btnTerpilih = e.target.closest('#btnBayarTerpilih');
        if (btnTerpilih) {
            e.preventDefault();
            openKonfirmasiBayar('terpilih');
            return;
        }

        var btnSemua = e.target.closest('#btnBayarSemua');
        if (btnSemua) {
            e.preventDefault();
            openKonfirmasiBayar('semua');
            return;
        }
    });

    function renderKonfirmasiBayarList(pilihan) {
        if (!konfirmasiBayarList || !konfirmasiBayarListWrap) return;

        konfirmasiBayarList.textContent = '';

        if (pilihan.terpilih.length === 0) {
            konfirmasiBayarListWrap.classList.add('hidden');
            return;
        }

        konfirmasiBayarListWrap.classList.remove('hidden');

        for (var i = 0; i < pilihan.terpilih.length; i++) {
            var item = pilihan.terpilih[i];

            var row = document.createElement('div');
            row.className = 'flex items-center justify-between gap-3 px-4 py-2.5';
            if (!item.bolehBayar) row.className += ' bg-amber-50/30';

            var info = document.createElement('div');
            info.className = 'min-w-0';

            var nama = document.createElement('span');
            nama.className = 'block text-sm font-semibold text-slate-800 truncate';
            nama.textContent = item.nama || '(tanpa nama)';
            info.appendChild(nama);

            var sub = document.createElement('span');
            sub.className = 'block text-xs text-slate-400 truncate';
            sub.textContent = (item.idKaryawan || '-') + ' · ' + item.status;
            info.appendChild(sub);
            row.appendChild(info);

            var kanan = document.createElement('div');
            kanan.className = 'text-right shrink-0';

            var nominal = document.createElement('span');
            nominal.className = 'block text-sm font-bold ' + (item.bolehBayar ? 'text-slate-700' : 'text-slate-400 line-through');
            nominal.textContent = 'Rp ' + Math.round(item.total || 0).toLocaleString('id-ID');
            kanan.appendChild(nominal);

            var badge = document.createElement('span');
            badge.className = 'inline-block mt-0.5 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full '
                + (item.bolehBayar ? 'text-emerald-800 bg-emerald-100' : 'text-amber-800 bg-amber-100');
            badge.textContent = !item.bolehBayar
                ? 'Dilewati'
                : (item.status === 'Draft' ? 'Akan Disahkan & Dibayar' : 'Akan Dibayar');
            kanan.appendChild(badge);

            row.appendChild(kanan);
            konfirmasiBayarList.appendChild(row);
        }
    }

    function renderKonfirmasiBayarPeringatan(pilihan) {
        if (!konfirmasiBayarPeringatan || !konfirmasiBayarPeringatanText) return;

        if (pilihan.jumlahDilewati === 0) {
            konfirmasiBayarPeringatan.classList.add('hidden');
            konfirmasiBayarPeringatanText.textContent = '';
            return;
        }

        var bagian = [];
        if (pilihan.jumlahDilewatiDibayar > 0) {
            bagian.push(pilihan.jumlahDilewatiDibayar + ' karyawan sudah dibayar');
        }
        if (pilihan.jumlahDilewatiDraft > 0) {
            bagian.push(pilihan.jumlahDilewatiDraft + ' karyawan tidak memenuhi syarat');
        }

        konfirmasiBayarPeringatanText.textContent = bagian.join(' dan ')
            + ' akan dilewati. Hanya ' + pilihan.jumlahBoleh + ' karyawan yang akan diproses.';
        konfirmasiBayarPeringatan.classList.remove('hidden');
    }

    function openKonfirmasiBayar(mode) {
        if (!konfirmasiBayarModal) return;
        modeBayar = mode;

        var pilihan = cekBayarChecked();
        var jumlah = 0;
        var total = 0;

        if (mode === 'terpilih') {
            if (pilihan.jumlah === 0) {
                alert('Pilih minimal satu karyawan terlebih dahulu.');
                return;
            }
            if (pilihan.jumlahBoleh === 0) {
                alert('Karyawan yang dipilih tidak dapat dibayar (sudah dibayar).');
                return;
            }
            jumlah = pilihan.jumlahBoleh;
            total = pilihan.totalBoleh;
            idBayarTerpilih = [];
            for (var i = 0; i < pilihan.terpilih.length; i++) {
                idBayarTerpilih.push(pilihan.terpilih[i].id);
            }
        } else {
            var btnSemuaEl = document.getElementById('btnBayarSemua');
            jumlah = Number(btnSemuaEl ? btnSemuaEl.dataset.jumlah : 0) || 0;
            konfirmasiBayarTotalSemua = (btnSemuaEl && btnSemuaEl.dataset.total) || '0';
            idBayarTerpilih = [];
            if (jumlah === 0) {
                alert('Tidak ada gaji yang belum dibayar pada periode ini.');
                return;
            }
        }

        konfirmasiBayarError.classList.add('hidden');
        processingBayar = false;
        if (btnKonfirmasiProsesBayar) {
            btnKonfirmasiProsesBayar.disabled = false;
            btnKonfirmasiProsesBayar.textContent = 'Proses Pembayaran';
        }

        var btnPeriode = document.getElementById('btnBayarSemua');
        byId('konfirmasiBayarPeriode').textContent = (btnPeriode && btnPeriode.dataset.periode) || '-';
        byId('konfirmasiBayarJumlah').textContent = Number(jumlah || 0).toLocaleString('id-ID') + ' Karyawan Dipilih';
        byId('konfirmasiBayarTotal').textContent = mode === 'terpilih'
            ? 'Rp ' + Math.round(total || 0).toLocaleString('id-ID')
            : 'Rp ' + (konfirmasiBayarTotalSemua || '0');
        byId('konfirmasiBayarPesan').textContent = mode === 'terpilih'
            ? 'Yakin ingin membayar gaji karyawan yang dipilih?'
            : 'Yakin ingin membayar seluruh gaji karyawan yang belum dibayar pada periode ini?';

        if (mode === 'terpilih') {
            renderKonfirmasiBayarList(pilihan);
            renderKonfirmasiBayarPeringatan(pilihan);
        } else {
            if (konfirmasiBayarListWrap) konfirmasiBayarListWrap.classList.add('hidden');
            if (konfirmasiBayarPeringatan) konfirmasiBayarPeringatan.classList.add('hidden');
        }

        konfirmasiBayarModal.classList.remove('hidden');
        konfirmasiBayarModal.classList.add('flex');
    }

    function closeKonfirmasiBayar() {
        if (!konfirmasiBayarModal) return;
        konfirmasiBayarModal.classList.add('hidden');
        konfirmasiBayarModal.classList.remove('flex');
    }

    if (btnKonfirmasiProsesBayar) {
        btnKonfirmasiProsesBayar.addEventListener('click', function () {
            if (processingBayar || btnKonfirmasiProsesBayar.disabled) return;
            processingBayar = true;
            var teksTombolKonfirmasi = btnKonfirmasiProsesBayar.textContent;
            btnKonfirmasiProsesBayar.disabled = true;
            btnKonfirmasiProsesBayar.textContent = 'Memproses\u2026';
            konfirmasiBayarError.classList.add('hidden');

            var p = periodeTerpilih();
            var fd = new FormData();
            fd.append('bulan', p.bulan);
            fd.append('tahun', p.tahun);

            var url = '/admin/penggajian/bayar-semua';
            if (modeBayar === 'terpilih') {
                url = '/admin/penggajian/bayar-terpilih';
                for (var i = 0; i < idBayarTerpilih.length; i++) {
                    fd.append('ids[]', idBayarTerpilih[i]);
                }
            }

            fetch(url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: fd
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { message: 'Terjadi kesalahan pada server.' };
                    }).then(function (data) {
                        if (res.ok) {
                            alert(data.message || 'Pembayaran gaji berhasil.');
                            closeKonfirmasiBayar();
                            window.location.href = urlDenganPeriode(p.bulan, p.tahun);
                        } else {
                            konfirmasiBayarErrorText.textContent = data.message || 'Terjadi kesalahan pada server.';
                            konfirmasiBayarError.classList.remove('hidden');
                            btnKonfirmasiProsesBayar.disabled = false;
                        }
                    });
                })
                .catch(function () {
                    konfirmasiBayarErrorText.textContent = 'Terjadi kesalahan pada server.';
                    konfirmasiBayarError.classList.remove('hidden');
                    btnKonfirmasiProsesBayar.disabled = false;
                })
                .finally(function () {
                    processingBayar = false;
                    btnKonfirmasiProsesBayar.disabled = false;
                    if (teksTombolKonfirmasi !== null) btnKonfirmasiProsesBayar.textContent = teksTombolKonfirmasi;
                });
        });
    }

    if (konfirmasiBayarModal) {
        konfirmasiBayarModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-konfirmasi-bayar-modal]') && !processingBayar) {
                closeKonfirmasiBayar();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !konfirmasiBayarModal.classList.contains('hidden') && !processingBayar) {
                closeKonfirmasiBayar();
            }
        });
    }

    // ---------- Riwayat: pilih periode & karyawan (sumber periode sama dengan halaman Penggajian) ----------
    var riwayatPilihBulan = document.getElementById('riwayatPilihBulan');
    var riwayatPilihTahun = document.getElementById('riwayatPilihTahun');
    var riwayatPilihKaryawan = document.getElementById('riwayatPilihKaryawan');

    function periodeRiwayatTerpilih() {
        var sekarang = new Date();
        return {
            bulan: riwayatPilihBulan ? riwayatPilihBulan.value : String(sekarang.getMonth() + 1),
            tahun: riwayatPilihTahun ? riwayatPilihTahun.value : String(sekarang.getFullYear())
        };
    }

    function gantiPeriodeRiwayat() {
        var p = periodeRiwayatTerpilih();
        var url = urlDenganPeriode(p.bulan, p.tahun);
        if (riwayatPilihKaryawan) {
            var u = new URL(url);
            if (riwayatPilihKaryawan.value && riwayatPilihKaryawan.value !== '0') {
                u.searchParams.set('karyawan', riwayatPilihKaryawan.value);
            } else {
                u.searchParams.delete('karyawan');
            }
            url = u.toString();
        }
        window.location.href = url;
    }

    if (riwayatPilihBulan) riwayatPilihBulan.addEventListener('change', gantiPeriodeRiwayat);
    if (riwayatPilihTahun) riwayatPilihTahun.addEventListener('change', gantiPeriodeRiwayat);
    if (riwayatPilihKaryawan) riwayatPilihKaryawan.addEventListener('change', gantiPeriodeRiwayat);

    // ---------- Laporan: buat laporan (pilih periode) ----------
    var laporanModal = document.getElementById('laporanModal');
    var laporanForm = document.getElementById('laporanForm');
    var laporanFormError = document.getElementById('laporanFormError');
    var laporanJudulInfo = document.getElementById('laporanJudulInfo');
    var laporanBulanWrap = document.getElementById('laporanBulanWrap');
    var laporanInfoKaryawan = document.getElementById('laporanInfoKaryawan');
    var laporanBulan = document.getElementById('laporanBulan');
    var laporanTahun = document.getElementById('laporanTahun');
    var btnSubmitLaporan = document.getElementById('btnSubmitLaporan');
    var btnCetakLaporan = document.getElementById('btnCetakLaporan');
    var laporanJenis = null;

    function openLaporanModal(jenis) {
        if (!laporanModal) return;
        laporanJenis = jenis;
        laporanFormError.classList.add('hidden');

        var info = {
            gaji_bulanan: 'Rekap rincian gaji seluruh karyawan pada bulan & tahun yang dipilih.',
            rekap_tahunan: 'Ringkasan total penggajian per bulan pada tahun yang dipilih.',
            data_karyawan: 'Daftar lengkap karyawan beserta data pekerjaan dan status.'
        }[jenis] || '';
        laporanJudulInfo.textContent = info;

        if (laporanBulanWrap) laporanBulanWrap.style.display = jenis === 'gaji_bulanan' ? '' : 'none';
        if (laporanInfoKaryawan) laporanInfoKaryawan.classList.toggle('hidden', jenis !== 'data_karyawan');
        if (btnCetakLaporan) btnCetakLaporan.closest('div').style.display = jenis === 'gaji_bulanan' ? '' : 'none';

        laporanModal.classList.remove('hidden');
        laporanModal.classList.add('flex');
    }

    function closeLaporanModal() {
        if (!laporanModal) return;
        laporanModal.classList.add('hidden');
        laporanModal.classList.remove('flex');
    }

    var iframeCetak = null;

    function hapusIframeCetak() {
        if (iframeCetak && iframeCetak.parentNode) {
            iframeCetak.parentNode.removeChild(iframeCetak);
        }
        iframeCetak = null;
    }

    function jalankanCetak() {
        if (!iframeCetak) return;
        var win = iframeCetak.contentWindow;
        if (!win) return;
        try {
            win.focus();
        } catch (e) {}
        setTimeout(function () {
            try {
                win.print();
            } catch (e) {}
            setTimeout(hapusIframeCetak, 1200);
        }, 250);
    }

    if (btnCetakLaporan) {
        btnCetakLaporan.addEventListener('click', function () {
            if (!laporanModal || laporanModal.classList.contains('hidden')) return;
            if (laporanJenis !== 'gaji_bulanan') return;

            laporanFormError.classList.add('hidden');

            var bulan = laporanBulan ? laporanBulan.value : String(new Date().getMonth() + 1);
            var tahun = laporanTahun ? laporanTahun.value : String(new Date().getFullYear());

            btnCetakLaporan.disabled = true;
            hapusIframeCetak();

            fetch('/admin/laporan/cetak?bulan=' + encodeURIComponent(bulan) + '&tahun=' + encodeURIComponent(tahun), {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (res) {
                    if (!res.ok) throw new Error('tidak_ada_data');
                    return res.text();
                })
                .then(function (html) {
                    closeLaporanModal();

                    iframeCetak = document.createElement('iframe');
                    iframeCetak.setAttribute('aria-hidden', 'true');
                    iframeCetak.style.cssText = 'position:fixed;left:-10000px;top:0;width:297mm;height:210mm;border:0;background:#fff;';
                    iframeCetak.addEventListener('load', jalankanCetak);
                    document.body.appendChild(iframeCetak);

                    var doc = iframeCetak.contentDocument || iframeCetak.contentWindow.document;
                    doc.open();
                    doc.write(html);
                    doc.close();
                })
                .catch(function () {
                    laporanFormError.textContent = 'Tidak ada data penggajian untuk periode terpilih. Pilih bulan & tahun yang memiliki gaji diproses.';
                    laporanFormError.classList.remove('hidden');
                })
                .finally(function () {
                    btnCetakLaporan.disabled = false;
                });
        });
    }

    document.querySelectorAll('.btn-buat-laporan').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openLaporanModal(btn.dataset.jenis);
        });
    });

    if (laporanModal) {
        laporanModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-laporan-modal]')) closeLaporanModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !laporanModal.classList.contains('hidden')) closeLaporanModal();
        });
    }

    if (laporanForm) {
        laporanForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!laporanModal || laporanModal.classList.contains('hidden')) return;

            laporanFormError.classList.add('hidden');
            btnSubmitLaporan.disabled = true;

            var body = { jenis: laporanJenis, tahun: laporanTahun ? laporanTahun.value : String(new Date().getFullYear()) };
            if (laporanJenis === 'gaji_bulanan' && laporanBulan) body.bulan = laporanBulan.value;
            var formatChecked = laporanForm.querySelector('input[name="format"]:checked');
            if (formatChecked) body.format = formatChecked.value;

            fetch('/admin/laporan', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { message: 'Terjadi kesalahan pada server.' };
                    }).then(function (data) {
                        if (res.ok && data.id) {
                            alert(data.message);
                            closeLaporanModal();
                            if (data.print_url) {
                                window.location.href = data.print_url;
                                return;
                            }
                            if (data.unduh_url) {
                                var a = document.createElement('a');
                                a.href = data.unduh_url;
                                a.download = data.nama_file || '';
                                document.body.appendChild(a);
                                a.click();
                                a.remove();
                            }
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            var messages = [];
                            var errors = data.errors || {};
                            Object.keys(errors).forEach(function (key) {
                                (errors[key] || []).forEach(function (msg) {
                                    messages.push(msg);
                                });
                            });
                            laporanFormError.textContent = messages.length ? messages.join(' ') : (data.message || 'Terjadi kesalahan pada server.');
                            laporanFormError.classList.remove('hidden');
                        }
                    });
                })
                .catch(function () {
                    laporanFormError.textContent = 'Terjadi kesalahan pada server.';
                    laporanFormError.classList.remove('hidden');
                })
                .finally(function () {
                    btnSubmitLaporan.disabled = false;
                });
        });
    }

    // ---------- Laporan: filter periode/jabatan/karyawan + preview + ekspor ----------
    var laporanFilter = document.getElementById('laporanFilter');
    if (laporanFilter) {
        var lfBulan = document.getElementById('laporanFilterBulan');
        var lfTahun = document.getElementById('laporanFilterTahun');
        var lfJabatan = document.getElementById('laporanFilterJabatan');
        var lfKaryawan = document.getElementById('laporanFilterKaryawan');
        var lfBody = document.getElementById('laporanPreviewBody');
        var lfFoot = document.getElementById('laporanPreviewFoot');
        var lfInfo = document.getElementById('laporanPreviewInfo');
        var btnLfCetak = document.getElementById('btnLaporanCetak');
        var btnLfPdf = document.getElementById('btnLaporanPdf');
        var btnLfExcel = document.getElementById('btnLaporanExcel');

        function lfAngka(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        }

        function lfQuery() {
            var params = new URLSearchParams();
            params.set('bulan', lfBulan ? lfBulan.value : String(new Date().getMonth() + 1));
            params.set('tahun', lfTahun ? lfTahun.value : String(new Date().getFullYear()));
            if (lfJabatan && lfJabatan.value) params.set('jabatan', lfJabatan.value);
            if (lfKaryawan && lfKaryawan.value && lfKaryawan.value !== '0') params.set('karyawan', lfKaryawan.value);
            return params;
        }

        function lfSaringKaryawan() {
            if (!lfJabatan || !lfKaryawan) return;
            var jabatan = lfJabatan.value;
            Array.prototype.forEach.call(lfKaryawan.options, function (opt) {
                if (opt.value === '0') {
                    opt.hidden = false;
                    opt.disabled = false;
                    return;
                }
                var cocok = jabatan === '' || (opt.dataset.jabatan || '') === jabatan;
                opt.hidden = !cocok;
                opt.disabled = !cocok;
            });
            var terpilih = lfKaryawan.options[lfKaryawan.selectedIndex];
            if (terpilih && (terpilih.hidden || terpilih.disabled)) {
                lfKaryawan.value = '0';
            }
        }

        function lfRender(data) {
            if (!lfBody) return;

            if (!data.baris || data.baris.length === 0) {
                lfBody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500">Tidak ada data penggajian untuk filter ini.</td></tr>';
                if (lfFoot) {
                    lfFoot.innerHTML = '';
                    lfFoot.classList.add('hidden');
                }
                if (lfInfo) lfInfo.textContent = 'Tidak ada data pada ' + data.periode_teks + (data.filter_label ? ' (' + data.filter_label + ')' : '') + '.';
                return;
            }

            var html = '';
            data.baris.forEach(function (r) {
                html += '<tr class="hover:bg-slate-50/50 transition-colors">'
                    + '<td class="px-3 py-3 text-sm text-slate-500">' + r.no + '</td>'
                    + '<td class="px-3 py-3"><span class="font-semibold text-slate-800">' + escHtml(r.nama) + '</span><span class="block text-xs text-slate-400">' + escHtml(r.id_karyawan) + ' • ' + escHtml(r.departemen) + '</span></td>'
                    + '<td class="px-3 py-3 text-sm text-slate-600">' + escHtml(r.jabatan) + '</td>'
                    + '<td class="px-3 py-3 text-sm text-slate-600 text-right whitespace-nowrap">' + lfAngka(r.gaji_pokok) + '</td>'
                    + '<td class="px-3 py-3 text-sm text-green-600 text-right whitespace-nowrap">+' + lfAngka(r.pembayaran) + '</td>'
                    + '<td class="px-3 py-3 text-sm text-red-500 text-right whitespace-nowrap">-' + lfAngka(r.pengurangan) + '</td>'
                    + '<td class="px-3 py-3 font-bold text-slate-800 text-right whitespace-nowrap">' + lfAngka(r.gaji_bersih) + '</td>'
                    + '<td class="px-3 py-3">' + (r.dibayar
                        ? '<span class="text-[10px] uppercase font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">Dibayar</span>'
                        : '<span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">' + escHtml(r.status) + '</span>') + '</td>'
                    + '<td class="px-3 py-3 text-right whitespace-nowrap"><a href="' + r.slip_url + '" target="_blank" rel="noopener" class="inline-flex items-center text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">Slip</a></td>'
                    + '</tr>';
            });
            lfBody.innerHTML = html;

            if (lfFoot) {
                lfFoot.classList.remove('hidden');
                lfFoot.innerHTML = '<tr>'
                    + '<td colspan="3" class="px-3 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Total ' + data.jumlah + ' karyawan</td>'
                    + '<td class="px-3 py-3 font-bold text-slate-800 text-right whitespace-nowrap">' + lfAngka(data.total_gaji_pokok) + '</td>'
                    + '<td class="px-3 py-3 font-bold text-green-700 text-right whitespace-nowrap">+' + lfAngka(data.total_pembayaran) + '</td>'
                    + '<td class="px-3 py-3 font-bold text-red-600 text-right whitespace-nowrap">-' + lfAngka(data.total_pengurangan) + '</td>'
                    + '<td class="px-3 py-3 font-bold text-slate-900 text-right whitespace-nowrap">' + lfAngka(data.total) + '</td>'
                    + '<td colspan="2"></td>'
                    + '</tr>';
            }

            if (lfInfo) {
                lfInfo.textContent = 'Menampilkan ' + data.jumlah + ' karyawan pada ' + data.periode_teks
                    + (data.filter_label ? ' (' + data.filter_label + ')' : '')
                    + '. Total Gaji Bersih: Rp ' + lfAngka(data.total) + '.';
            }
        }

        var lfReqId = 0;

        function lfMuat() {
            if (lfInfo) lfInfo.textContent = 'Memuat data…';
            var url = laporanFilter.dataset.previewUrl + '?' + lfQuery().toString();
            var rid = ++lfReqId;

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (rid !== lfReqId) return;
                    lfRender(data);
                })
                .catch(function () {
                    if (rid !== lfReqId) return;
                    if (lfInfo) lfInfo.textContent = '';
                    if (lfBody) lfBody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-sm text-red-500">Gagal memuat data laporan.</td></tr>';
                });
        }

        function lfEkspor(jenis) {
            var base = jenis === 'pdf' ? laporanFilter.dataset.pdfUrl : laporanFilter.dataset.excelUrl;
            window.location.href = base + '?' + lfQuery().toString();
        }

        function lfCetak() {
            var url = laporanFilter.dataset.cetakUrl + '?' + lfQuery().toString() + '&auto=1';
            var win = window.open(url, '_blank');
            if (!win) window.location.href = url;
        }

        [lfBulan, lfTahun, lfJabatan, lfKaryawan].forEach(function (el) {
            if (!el) return;
            el.addEventListener('change', function () {
                if (el === lfJabatan) lfSaringKaryawan();
                lfMuat();
            });
        });

        if (btnLfCetak) btnLfCetak.addEventListener('click', lfCetak);
        if (btnLfPdf) btnLfPdf.addEventListener('click', function () { lfEkspor('pdf'); });
        if (btnLfExcel) btnLfExcel.addEventListener('click', function () { lfEkspor('excel'); });

        lfSaringKaryawan();
        lfMuat();
    }

    // ---------- Profil: edit modal ----------
    var profilModal = document.getElementById('profilModal');
    var profilForm = document.getElementById('profilForm');
    var profilFormError = document.getElementById('profilFormError');
    var profilFormErrorText = document.getElementById('profilFormErrorText');
    var btnEditProfile = document.getElementById('btnEditProfile');

    function openProfilModal() {
        if (!profilModal) return;
        profilFormError.classList.add('hidden');
        resetFotoProfilPreview();
        profilModal.classList.remove('hidden');
        profilModal.classList.add('flex');
    }

    function resetFotoProfilPreview() {
        var input = document.getElementById('profil_foto');
        var img = document.getElementById('profilFotoPreview');
        var ph = document.getElementById('profilFotoPlaceholder');
        if (input) input.value = '';
        if (img) {
            img.src = img.dataset.current || '';
            if (img.src) {
                img.classList.remove('hidden');
                if (ph) ph.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                if (ph) ph.classList.remove('hidden');
            }
        }
    }

    if (btnEditProfile) {
        btnEditProfile.addEventListener('click', openProfilModal);
    }

    var btnPilihFotoProfil = document.getElementById('btnPilihFotoProfil');
    var inputFotoProfil = document.getElementById('profil_foto');

    if (btnPilihFotoProfil) {
        btnPilihFotoProfil.addEventListener('click', function () {
            if (inputFotoProfil) inputFotoProfil.click();
        });
    }

    if (inputFotoProfil) {
        inputFotoProfil.addEventListener('change', function () {
            var file = inputFotoProfil.files[0];
            if (!file) return;

            if (!/\.(jpe?g|png)$/i.test(file.name)) {
                inputFotoProfil.value = '';
                alert('Format foto tidak didukung. Gunakan file JPG, JPEG, atau PNG.');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                inputFotoProfil.value = '';
                alert('Ukuran foto maksimal 2 MB.');
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.getElementById('profilFotoPreview');
                var ph = document.getElementById('profilFotoPlaceholder');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                if (ph) ph.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    }

    if (profilModal) {
        profilModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-profil-modal]')) {
                profilModal.classList.add('hidden');
                profilModal.classList.remove('flex');
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !profilModal.classList.contains('hidden')) {
                profilModal.classList.add('hidden');
                profilModal.classList.remove('flex');
            }
        });
    }

    if (profilForm) {
        profilForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!profilModal || profilModal.classList.contains('hidden')) return;

            var fd = new FormData(profilForm);
            fd.append('_method', 'PUT');
            profilFormError.classList.add('hidden');

            fetch('/profil', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { message: 'Terjadi kesalahan pada server.' };
                    }).then(function (data) {
                        if (res.ok) {
                            alert(data.message || 'Profil berhasil diperbarui.');
                            window.location.reload();
                        } else {
                            var messages = [];
                            var errors = data.errors || {};
                            Object.keys(errors).forEach(function (key) {
                                (errors[key] || []).forEach(function (msg) {
                                    messages.push(msg);
                                });
                            });
                            profilFormErrorText.textContent = messages.length ? messages.join(' ') : (data.message || 'Terjadi kesalahan pada server.');
                            profilFormError.classList.remove('hidden');
                        }
                    });
                })
                .catch(function () {
                    profilFormErrorText.textContent = 'Terjadi kesalahan pada server.';
                    profilFormError.classList.remove('hidden');
                });
        });
    }

    // ---------- Profil: ubah password modal ----------
    var passwordModal = document.getElementById('passwordModal');
    var passwordForm = document.getElementById('passwordForm');
    var passwordFormError = document.getElementById('passwordFormError');
    var passwordFormErrorText = document.getElementById('passwordFormErrorText');
    var btnUbahPassword = document.getElementById('btnUbahPassword');

    function openPasswordModal() {
        if (!passwordModal) return;
        if (passwordForm) passwordForm.reset();
        if (passwordFormError) passwordFormError.classList.add('hidden');
        passwordModal.classList.remove('hidden');
        passwordModal.classList.add('flex');
    }

    function closePasswordModal() {
        if (!passwordModal) return;
        passwordModal.classList.add('hidden');
        passwordModal.classList.remove('flex');
    }

    if (btnUbahPassword) {
        btnUbahPassword.addEventListener('click', openPasswordModal);
    }

    if (passwordModal) {
        passwordModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-password-modal]')) {
                closePasswordModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !passwordModal.classList.contains('hidden')) {
                closePasswordModal();
            }
        });
    }

    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var fd = new FormData(passwordForm);
            fd.append('_method', 'PUT');
            if (passwordFormError) passwordFormError.classList.add('hidden');

            fetch('/profil/password', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        return { message: 'Terjadi kesalahan pada server.' };
                    }).then(function (data) {
                        if (res.ok) {
                            alert(data.message || 'Password berhasil diubah.');
                            closePasswordModal();
                            passwordForm.reset();
                        } else {
                            var messages = [];
                            var errors = data.errors || {};
                            Object.keys(errors).forEach(function (key) {
                                (errors[key] || []).forEach(function (msg) {
                                    messages.push(msg);
                                });
                            });
                            passwordFormErrorText.textContent = messages.length ? messages.join(' ') : (data.message || 'Terjadi kesalahan pada server.');
                            passwordFormError.classList.remove('hidden');
                        }
                    });
                })
                .catch(function () {
                    passwordFormErrorText.textContent = 'Terjadi kesalahan pada server.';
                    passwordFormError.classList.remove('hidden');
                });
        });
    }

    // ---------- Penggajian: detail satu gaji ----------
    var detailGajiModal = document.getElementById('detailGajiModal');
    var detailData = window.PENGGAJIAN_DETAILS || {};

    function initialsOf(nama) {
        return (nama || '?').split(' ').map(function (w) { return w.charAt(0); }).join('').slice(0, 2).toUpperCase();
    }

    function byId(id) {
        return document.getElementById(id);
    }

    function escHtml(nilai) {
        return String(nilai == null ? '' : nilai)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function detailBarisRincian(items) {
        var daftar = (items || []).filter(function (it) {
            return (Number(it.nominal) || 0) !== 0 || it.keterangan;
        });

        if (!daftar.length) {
            return '<p class="text-xs text-slate-400 italic">Tidak ada</p>';
        }

        return daftar.map(function (it) {
            var nominal = Number(it.nominal) || 0;
            var keterangan = it.keterangan
                ? '<span class="block text-xs text-slate-400">' + escHtml(it.keterangan) + '</span>'
                : '';
            return '<div class="flex items-start justify-between gap-3">'
                + '<span class="text-slate-600">' + escHtml(it.label) + keterangan + '</span>'
                + '<span class="font-medium text-slate-700 whitespace-nowrap">Rp ' + nominal.toLocaleString('id-ID') + '</span>'
                + '</div>';
        }).join('');
    }

    function openDetailGaji(penggajianId) {
        var d = detailData[penggajianId];
        if (!detailGajiModal || !d) return;

        var statusLabel, badgeTheme;
        if (d.status_raw === 'Draft') {
            statusLabel = 'DRAFT';
            badgeTheme = 'bg-amber-100 text-amber-700 border-amber-200';
        } else if (d.status_raw === 'Disahkan') {
            statusLabel = 'DISAHKAN';
            badgeTheme = 'bg-violet-100 text-violet-700 border-violet-200';
        } else if (d.dibayar) {
            statusLabel = 'SUDAH DIBAYAR';
            badgeTheme = 'bg-emerald-100 text-emerald-700 border-emerald-200';
        } else {
            statusLabel = 'BELUM DIBAYAR';
            badgeTheme = 'bg-blue-100 text-blue-700 border-blue-200';
        }

        var tanggalBayar = (d.dibayar && d.tanggal_pembayaran)
            ? '<span class="block text-[10px] font-medium text-slate-500 mt-1">' + escHtml(d.tanggal_pembayaran) + '</span>'
            : '';

        byId('detailStatusBadge').innerHTML =
            '<span class="inline-flex items-center gap-1 text-[10px] uppercase font-bold border px-2.5 py-1 rounded-full ' + badgeTheme + '">' +
                escHtml(statusLabel) +
            '</span>' + tanggalBayar;

        var avatar = byId('detailNamaAvatar');
        if (avatar) { avatar.textContent = initialsOf(d.nama); }

        byId('detailNama').textContent = d.nama;
        byId('detailIdKaryawan').textContent = d.id_karyawan;
        byId('detailDepartemen').textContent = d.departemen;
        if (byId('detailJabatan')) byId('detailJabatan').textContent = d.jabatan || '-';
        byId('detailStatusPeran').textContent = d.status_peran || '-';
        byId('detailPeriode').textContent = d.periode;
        byId('detailPeriodeGaji').textContent = d.periode_gaji || '-';
        byId('detailBank').textContent = d.nama_bank || '-';
        byId('detailNomorRekening').textContent = d.nomor_rekening || '-';
        byId('detailNamaRekening').textContent = d.nama_pemilik_rekening || '-';
        byId('detailGajiPokok').textContent = 'Rp ' + d.gaji_pokok;
        byId('detailPembayaran').textContent = '+Rp ' + d.pembayaran;
        byId('detailPengurangan').textContent = '-Rp ' + d.pengurangan;
        byId('detailBersih').textContent = 'Rp ' + d.bersih;
        byId('detailRincianPembayaran').innerHTML = detailBarisRincian(d.rincian_pembayaran);
        byId('detailRincianPengurangan').innerHTML = detailBarisRincian(d.rincian_pengurangan);

        var adaRekening = [d.nama_bank, d.nomor_rekening, d.nama_pemilik_rekening].some(function (v) {
            return v && v !== '-';
        });
        var rekeningIsi = byId('detailRekeningIsi');
        var rekeningKosong = byId('detailRekeningKosong');
        if (rekeningIsi) rekeningIsi.classList.toggle('hidden', !adaRekening);
        if (rekeningKosong) rekeningKosong.classList.toggle('hidden', adaRekening);

        var cetak = byId('detailCetakSlip');
        if (cetak) {
            if (d.dibayar && d.cetak_url) {
                cetak.href = d.cetak_url;
                cetak.classList.remove('hidden');
            } else {
                cetak.classList.add('hidden');
            }
        }

        detailGajiModal.classList.remove('hidden');
        detailGajiModal.classList.add('flex');
    }

    function closeDetailGaji() {
        if (!detailGajiModal) return;
        detailGajiModal.classList.add('hidden');
        detailGajiModal.classList.remove('flex');
    }

    document.addEventListener('click', function (e) {
        var detailBtn = e.target.closest('.btn-detail-gaji');
        if (detailBtn && detailBtn.dataset.id) {
            tutupAksiMenu();
            openDetailGaji(detailBtn.dataset.id);
        }
    });

    if (detailGajiModal) {
        detailGajiModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-detail-gaji-modal]')) {
                closeDetailGaji();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !detailGajiModal.classList.contains('hidden')) {
                closeDetailGaji();
            }
        });
    }

    // ---------- Penggajian: modal slip gaji (struk) ----------
    var slipModal = document.getElementById('slipModal');
    var slipKaryawanSelect = document.getElementById('slipKaryawanSelect');
    var slipPeriodeSelect = document.getElementById('slipPeriodeSelect');
    var slipState = { items: [], byKaryawan: {}, karyawanUrut: [], karyawanId: null, index: -1, req: 0, data: null, trigger: null };

    function slipRp(nilai) {
        return 'Rp ' + (Number(nilai) || 0).toLocaleString('id-ID');
    }

    function slipTampilkanMuat(show, error) {
        byId('slipMuatSpinner').classList.toggle('hidden', !show);
        byId('slipMuatSpinner').classList.toggle('flex', !!show);
        byId('slipMuatError').classList.toggle('hidden', !error);
        byId('slipMuatError').classList.toggle('flex', !!error);
        byId('slipKonten').classList.toggle('hidden', !!show || !!error);
    }

    function slipAmbilInventaris() {
        if (slipState.items.length) {
            return Promise.resolve(slipState.items);
        }
        return fetch(slipModal.dataset.inventarisUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function (json) {
            var items = json.items || [];
            slipState.items = items;
            slipState.byKaryawan = {};
            slipState.karyawanUrut = [];
            items.forEach(function (it) {
                if (!slipState.byKaryawan[it.karyawan_id]) {
                    slipState.byKaryawan[it.karyawan_id] = [];
                    slipState.karyawanUrut.push({ id: it.karyawan_id, nama: it.nama, id_karyawan: it.id_karyawan });
                }
                slipState.byKaryawan[it.karyawan_id].push(it);
            });
            Object.keys(slipState.byKaryawan).forEach(function (k) {
                slipState.byKaryawan[k].sort(function (a, b) {
                    return a.periode < b.periode ? -1 : a.periode > b.periode ? 1 : 0;
                });
            });
            slipIsiSelectKaryawan();
            return items;
        });
    }

    function slipIsiSelectKaryawan() {
        slipKaryawanSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih karyawan…';
        slipKaryawanSelect.appendChild(placeholder);
        slipState.karyawanUrut.forEach(function (k) {
            var opt = document.createElement('option');
            opt.value = String(k.id);
            opt.textContent = k.nama + ' (' + k.id_karyawan + ')';
            slipKaryawanSelect.appendChild(opt);
        });
    }

    function slipIsiSelectPeriode(daftar, pilihId) {
        slipPeriodeSelect.innerHTML = '';
        (daftar || []).forEach(function (it) {
            var opt = document.createElement('option');
            opt.value = String(it.id);
            opt.textContent = it.periode_label;
            slipPeriodeSelect.appendChild(opt);
        });
        if (pilihId) {
            slipPeriodeSelect.value = String(pilihId);
        }
    }

    function slipDaftarAktif() {
        return slipState.byKaryawan[slipState.karyawanId] || [];
    }

    function slipPerbaruiNavigasi() {
        var daftar = slipDaftarAktif();
        byId('slipPosisi').textContent = daftar.length
            ? 'Periode ' + (slipState.index + 1) + ' dari ' + daftar.length
            : '—';
        byId('slipPrevBtn').disabled = slipState.index <= 0;
        byId('slipNextBtn').disabled = slipState.index < 0 || slipState.index >= daftar.length - 1;
    }

    function slipPilih(karyawanId, penggajianId) {
        slipState.karyawanId = karyawanId;
        slipKaryawanSelect.value = String(karyawanId);
        var daftar = slipState.byKaryawan[karyawanId] || [];
        slipIsiSelectPeriode(daftar, penggajianId);
        slipState.index = -1;
        daftar.forEach(function (it, i) {
            if (Number(it.id) === Number(penggajianId)) {
                slipState.index = i;
                slipPeriodeSelect.value = String(it.id);
            }
        });
        slipPerbaruiNavigasi();
    }

    function slipBarisTabel(items, tandaMinus) {
        var daftar = (items || []).filter(function (it) {
            return (Number(it.nominal) || 0) !== 0 || it.keterangan;
        });
        if (!daftar.length) {
            return '<tr><td style="color:#94a3b8;">Tidak ada.</td><td class="num">Rp 0</td></tr>';
        }
        return daftar.map(function (it) {
            var nominal = Number(it.nominal) || 0;
            var ket = it.keterangan ? '<span class="ket">' + escHtml(it.keterangan) + '</span>' : '';
            var nilai = (tandaMinus ? '-Rp ' : 'Rp ') + nominal.toLocaleString('id-ID');
            return '<tr><td>' + escHtml(it.label) + ket + '</td><td class="num">' + nilai + '</td></tr>';
        }).join('');
    }

    function slipRender(d) {
        slipState.data = d;

        byId('slipModalNomor').textContent = 'No. ' + d.nomor + ' · ' + d.identitas.nama;

        var statusLabel, badgeTheme;
        if (d.status_raw === 'Draft') {
            statusLabel = 'DRAFT';
            badgeTheme = 'bg-amber-100 text-amber-700 border-amber-200';
        } else if (d.status_raw === 'Disahkan') {
            statusLabel = 'DISAHKAN';
            badgeTheme = 'bg-violet-100 text-violet-700 border-violet-200';
        } else if (d.dibayar) {
            statusLabel = 'SUDAH DIBAYAR';
            badgeTheme = 'bg-emerald-100 text-emerald-700 border-emerald-200';
        } else {
            statusLabel = 'BELUM DIBAYAR';
            badgeTheme = 'bg-blue-100 text-blue-700 border-blue-200';
        }
        var badge = byId('slipStatusBadge');
        badge.className = 'inline-flex items-center gap-1 text-[10px] uppercase font-bold border px-2.5 py-1 rounded-full ' + badgeTheme;
        badge.textContent = statusLabel;

        var template = byId('slipSheetTemplate');
        var isi = template.content.firstElementChild.cloneNode(true);

        var set = function (id, nilai) {
            var el = isi.querySelector('#' + id);
            if (el) el.textContent = nilai;
        };

        set('slipIsiNomor', d.nomor);
        set('slipIsiPeriodeKop', d.periode_label);
        set('slipIsiNama', d.identitas.nama);
        set('slipIsiIdKaryawan', d.identitas.id_karyawan);
        set('slipIsiEmail', d.identitas.email || '-');
        set('slipIsiNoHp', d.identitas.no_hp || '-');
        set('slipIsiDepartemen', d.identitas.departemen || '-');
        set('slipIsiJabatan', d.identitas.jabatan || '-');
        set('slipIsiStatusPeran', d.identitas.status_peran || '-');
        set('slipIsiBank', d.identitas.nama_bank || '-');
        set('slipIsiRekening', d.identitas.nomor_rekening || '-');
        set('slipIsiAtasNama', d.identitas.nama_pemilik_rekening || '-');
        set('slipIsiPeriode', d.periode_label);
        set('slipIsiTanggalGaji', d.tanggal_penggajian || '-');
        set('slipIsiTanggalBayar', d.tanggal_pembayaran || '-');

        isi.querySelectorAll('[data-slip-opsional]').forEach(function (el) {
            var key = el.getAttribute('data-slip-opsional');
            var nilai = key === 'tanggal_pembayaran' ? d.tanggal_pembayaran : d.identitas[key];
            el.classList.toggle('hidden', !nilai);
        });

        isi.querySelector('#slipIsiPendapatanBody').innerHTML =
            '<tr><td>Gaji Pokok</td><td class="num">' + slipRp(d.gaji_pokok) + '</td></tr>'
            + slipBarisTabel(d.penerimaan, false);
        set('slipIsiTotalPendapatan', slipRp(d.gaji_pokok + d.total_penerimaan));
        isi.querySelector('#slipIsiPenguranganBody').innerHTML = slipBarisTabel(d.pengurangan, true);
        set('slipIsiTotalPengurangan', '-Rp ' + (Number(d.total_pengurangan) || 0).toLocaleString('id-ID'));
        set('slipIsiGajiBersih', slipRp(d.gaji_bersih));
        set('slipIsiNamaTtd', d.identitas.nama);
        set('slipIsiCetakPada', new Date().toLocaleString('id-ID'));

        var cetakArea = document.createElement('div');
        cetakArea.id = 'slipCetakArea';
        cetakArea.appendChild(isi);
        var konten = byId('slipKonten');
        konten.innerHTML = '';
        konten.appendChild(cetakArea);

        byId('slipUnduhLink').href = d.urls.unduh;

        var cetakBtn = byId('slipCetakBtn');
        cetakBtn.disabled = !d.dibayar;
        cetakBtn.title = d.dibayar
            ? 'Cetak struk gaji'
            : 'Hanya slip berstatus sudah dibayar yang dapat dicetak';
    }

    function slipMuatData(penggajianId, seq) {
        var url = slipModal.dataset.slipUrlTemplate.replace('{penggajian}', encodeURIComponent(penggajianId));
        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        }).then(function (d) {
            if (seq !== slipState.req) return;
            slipRender(d);
            slipTampilkanMuat(false, false);
        });
    }

    function slipMuat(penggajianId) {
        if (!slipModal) return;
        var seq = ++slipState.req;
        slipTampilkanMuat(true, false);

        slipAmbilInventaris().then(function (items) {
            if (seq !== slipState.req) return;
            var item = null;
            for (var i = 0; i < items.length; i++) {
                if (Number(items[i].id) === Number(penggajianId)) {
                    item = items[i];
                    break;
                }
            }
            if (!item) throw new Error('Slip tidak ditemukan.');
            slipPilih(item.karyawan_id, item.id);
            return slipMuatData(item.id, seq);
        }).catch(function () {
            if (seq !== slipState.req) return;
            slipTampilkanMuat(false, true);
        });
    }

    function slipBuka(penggajianId, trigger) {
        if (!slipModal) return;
        slipState.trigger = trigger || null;
        slipModal.classList.remove('hidden');
        slipModal.classList.add('flex');
        slipMuat(penggajianId);
    }

    function slipTutup() {
        if (!slipModal) return;
        slipModal.classList.add('hidden');
        slipModal.classList.remove('flex');
        if (slipState.trigger && slipState.trigger.focus) {
            try { slipState.trigger.focus(); } catch (err) { /* noop */ }
        }
        slipState.trigger = null;
    }

    if (slipModal) {
        slipIsiSelectKaryawan();

        document.addEventListener('click', function (e) {
            var slipBtn = e.target.closest('.btn-buka-slip');
            if (slipBtn && slipBtn.dataset.slipId) {
                tutupAksiMenu();
                slipBuka(slipBtn.dataset.slipId, slipBtn);
            }
        });

        slipModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-slip-modal]')) {
                slipTutup();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !slipModal.classList.contains('hidden')) {
                slipTutup();
            }
        });

        slipKaryawanSelect.addEventListener('change', function () {
            var kid = Number(this.value);
            if (!kid || !slipState.byKaryawan[kid]) return;
            var daftar = slipState.byKaryawan[kid];
            var periodeSaatIni = slipState.data ? slipState.data.periode : null;
            var pilih = null;
            daftar.forEach(function (it) {
                if (it.periode === periodeSaatIni) pilih = it;
            });
            if (!pilih && daftar.length) pilih = daftar[daftar.length - 1];
            if (pilih) slipMuat(pilih.id);
        });

        slipPeriodeSelect.addEventListener('change', function () {
            var id = Number(this.value);
            if (id) slipMuat(id);
        });

        byId('slipPrevBtn').addEventListener('click', function () {
            var daftar = slipDaftarAktif();
            if (slipState.index > 0) slipMuat(daftar[slipState.index - 1].id);
        });

        byId('slipNextBtn').addEventListener('click', function () {
            var daftar = slipDaftarAktif();
            if (slipState.index >= 0 && slipState.index < daftar.length - 1) {
                slipMuat(daftar[slipState.index + 1].id);
            }
        });

        byId('slipMuatUlang').addEventListener('click', function () {
            var daftar = slipDaftarAktif();
            if (slipState.index >= 0 && daftar[slipState.index]) {
                slipMuat(daftar[slipState.index].id);
            }
        });

        byId('slipCetakBtn').addEventListener('click', function () {
            if (this.disabled) return;
            document.body.classList.add('slip-printing');
            try { window.focus(); window.print(); } catch (err) { /* noop */ }
        });

        window.addEventListener('afterprint', function () {
            document.body.classList.remove('slip-printing');
        });
    }

    // ---------- Penggajian: kelola & koreksi rincian ----------
    var pgModal = document.getElementById('penggajianRincianModal');
    var pgRowTemplate = document.getElementById('pgRowTemplate');
    var pgState = { mode: null, id: null, form: null, processing: false, loading: false };

    function rupiah(n) {
        n = Number(n) || 0;
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    // Ubah teks apa pun menjadi angka bulat (buang pemisah ribuan / simbol Rp).
    function pgParseAngka(v) {
        var digits = String(v == null ? '' : v).replace(/[^\d]/g, '');
        return digits ? parseInt(digits, 10) : 0;
    }

    // Tampilkan angka sebagai rupiah berformat "1.000.000" (tanpa "Rp").
    function pgFormatAngka(v) {
        var n = Math.round(Number(v) || 0);
        if (!n) return '';
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Auto-format input rupiah sambil mempertahankan posisi kursor.
    function pgFormatInput(el) {
        var raw = el.value || '';
        var caret = el.selectionStart;
        var digitsBefore = (raw.slice(0, caret).match(/\d/g) || []).length;
        var digits = raw.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        var formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        el.value = formatted;
        if (document.activeElement === el) {
            var pos = 0;
            var count = 0;
            while (pos < formatted.length && count < digitsBefore) {
                if (/\d/.test(formatted.charAt(pos))) count++;
                pos++;
            }
            try { el.setSelectionRange(pos, pos); } catch (err) { /* noop */ }
        }
    }

    function pgSetError(msg) {
        var box = byId('pgModalError');
        var txt = byId('pgModalErrorText');
        if (!box || !txt) return;
        if (msg) {
            txt.textContent = msg;
            box.classList.remove('hidden');
        } else {
            txt.textContent = '';
            box.classList.add('hidden');
        }
    }

    function pgTerapkanBacaSaja(bacaSaja) {
        if (!pgModal) return;
        var fields = pgModal.querySelectorAll('.pg-kategori, .pg-nominal, .pg-keterangan, .pg-hapus, #pgAlasan, #pgPotonganKehadiran');
        for (var i = 0; i < fields.length; i++) fields[i].disabled = bacaSaja;
        ['btnTambahPembayaran', 'btnTambahPengurangan', 'btnSimpanRincian', 'btnSimpanKoreksi', 'btnProsesBayar'].forEach(function (id) {
            var el = byId(id);
            if (!el) return;
            el.disabled = bacaSaja;
            if (bacaSaja) el.classList.add('hidden');
        });
    }

    function pgWajibKeterangan(kategori) {
        var list = (pgState.form && pgState.form.wajib_keterangan) || [];
        return list.indexOf(kategori) !== -1;
    }

    function pgIsiKategori(select, daftar, terpilih) {
        select.innerHTML = '';
        daftar.forEach(function (k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = k;
            if (k === terpilih) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function pgTerapkanKeterangan(row) {
        if (!row) return;
        var wajib = pgWajibKeterangan(row.querySelector('.pg-kategori').value);
        var input = row.querySelector('.pg-keterangan');
        input.placeholder = wajib ? 'Wajib diisi' : 'Opsional';
        input.required = wajib;
    }

    function pgBuatBaris(tipe, data) {
        if (!pgRowTemplate) return null;
        var frag = pgRowTemplate.content.cloneNode(true);
        var row = frag.querySelector('tr');
        var select = row.querySelector('.pg-kategori');
        var nominal = row.querySelector('.pg-nominal');
        var keterangan = row.querySelector('.pg-keterangan');

        var daftar = tipe === 'pengurangan'
            ? (pgState.form.kategori_pengurangan || [])
            : (pgState.form.kategori_pembayaran || []);

        row.dataset.tipe = tipe;
        pgIsiKategori(select, daftar, data ? data.kategori : daftar[0]);
        nominal.value = data && data.nominal ? pgFormatAngka(data.nominal) : '';
        keterangan.value = data && data.keterangan ? data.keterangan : '';

        pgTerapkanKeterangan(row);
        return row;
    }

    function pgHitung() {
        if (!pgState.form) return;
        var pokok = Number(pgState.form.penggajian.gaji_pokok) || 0;
        var potonganKehadiran = pgParseAngka((byId('pgPotonganKehadiran') || {}).value);
        var pembayaran = 0;
        var pengurangan = 0;

        document.querySelectorAll('#pgTbodyPembayaran tr').forEach(function (row) {
            pembayaran += pgParseAngka(row.querySelector('.pg-nominal').value);
        });
        document.querySelectorAll('#pgTbodyPengurangan tr').forEach(function (row) {
            pengurangan += pgParseAngka(row.querySelector('.pg-nominal').value);
        });

        byId('pgModalPokok').textContent = rupiah(pokok);
        byId('pgModalPembayaran').textContent = rupiah(pembayaran);
        byId('pgModalPengurangan').textContent = rupiah(pengurangan);
        var kartuPotongan = byId('pgModalPotonganKehadiran');
        if (kartuPotongan) kartuPotongan.textContent = rupiah(potonganKehadiran);
        var kartuTotalPengurangan = byId('pgModalTotalPengurangan');
        if (kartuTotalPengurangan) kartuTotalPengurangan.textContent = rupiah(pengurangan + potonganKehadiran);
        byId('pgModalBersih').textContent = rupiah(pokok + pembayaran - pengurangan - potonganKehadiran);
    }

    function pgIsiBaris(data) {
        var tbodyPembayaran = byId('pgTbodyPembayaran');
        var tbodyPengurangan = byId('pgTbodyPengurangan');
        tbodyPembayaran.innerHTML = '';
        tbodyPengurangan.innerHTML = '';

        var inputPotongan = byId('pgPotonganKehadiran');
        if (inputPotongan) {
            var nilaiPotongan = data && data.penggajian ? Number(data.penggajian.potongan_kehadiran) || 0 : 0;
            inputPotongan.value = nilaiPotongan ? pgFormatAngka(nilaiPotongan) : '';
        }

        var pembayaran = [];
        var pengurangan = [];
        ((data && data.rincian) || []).forEach(function (r) {
            (r.tipe === 'pembayaran' ? pembayaran : pengurangan).push(r);
        });

        if (!pembayaran.length) pembayaran.push(null);
        if (!pengurangan.length) pengurangan.push(null);

        pembayaran.forEach(function (r) {
            var row = pgBuatBaris('pembayaran', r);
            if (row) tbodyPembayaran.appendChild(row);
        });
        pengurangan.forEach(function (r) {
            var row = pgBuatBaris('pengurangan', r);
            if (row) tbodyPengurangan.appendChild(row);
        });

        pgHitung();
    }

    function bukaPgModal(id, mode, trigger) {
        if (!pgModal || pgState.loading) return;
        pgState.loading = true;
        pgSetError('');

        var teksAsli = null;
        if (trigger) {
            teksAsli = trigger.textContent;
            trigger.disabled = true;
            trigger.textContent = 'Memuat\u2026';
        }

        fetch('/admin/penggajian/' + id + '/form', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                pgState.mode = mode;
                pgState.id = id;
                pgState.form = data;

                var judul = 'Kelola Penggajian';
                var hint = 'Simpan rincian terlebih dahulu, lalu ajukan pengesahan agar dapat dibayarkan.';

                if (mode === 'koreksi') {
                    judul = 'Koreksi Penggajian (' + data.penggajian.status_label + ')';
                    hint = 'Status saat ini: ' + data.penggajian.status_label + '. Isi Alasan Koreksi (wajib), sesuaikan rincian, lalu klik Simpan Koreksi. Perubahan dicatat sebagai snapshot audit (data sebelum & sesudah).';
                } else if (mode === 'bayar') {
                    judul = 'Proses Pembayaran Gaji';
                    hint = 'Periksa rincian, tambahkan komisi/bonus/kasbon bila perlu, lalu klik Proses Pembayaran. Status akan berubah menjadi Sudah Dibayar.';
                }

                byId('pgModalTitle').textContent = judul;
                byId('pgModalSubtitle').textContent = data.penggajian.karyawan.nama + ' (' + data.penggajian.karyawan.id_karyawan + ') \u00b7 ' + data.penggajian.karyawan.departemen;
                var elPeriode = byId('pgModalPeriode');
                if (elPeriode) elPeriode.textContent = 'Periode Gaji: ' + data.penggajian.periode_label;
                byId('pgModeHint').textContent = hint;

                var wrapKoreksi = byId('pgKoreksiWrap');
                if (wrapKoreksi) {
                    if (mode === 'koreksi') {
                        wrapKoreksi.classList.remove('hidden');
                        wrapKoreksi.style.display = 'block';
                    } else {
                        wrapKoreksi.classList.add('hidden');
                        wrapKoreksi.style.display = 'none';
                    }
                }

                var btnRincian = byId('btnSimpanRincian');
                if (btnRincian) btnRincian.classList.toggle('hidden', mode !== 'edit');

                var btnKoreksi = byId('btnSimpanKoreksi');
                if (btnKoreksi) {
                    btnKoreksi.classList.toggle('hidden', mode !== 'koreksi');
                    btnKoreksi.disabled = false;
                }

                var inputAlasan = byId('pgAlasan');
                if (inputAlasan) {
                    inputAlasan.value = '';
                    if (mode === 'koreksi' && btnKoreksi) btnKoreksi.disabled = true;
                }

                var btnBayarProses = byId('btnProsesBayar');
                if (btnBayarProses) btnBayarProses.classList.toggle('hidden', mode !== 'bayar');

                pgIsiBaris(data);

                var bacaSaja = mode === 'koreksi' && !data.penggajian.boleh_koreksi;
                pgTerapkanBacaSaja(bacaSaja);
                pgSetError(bacaSaja ? 'Penggajian yang sudah dibayar tidak dapat dikoreksi.' : '');

                pgModal.classList.remove('hidden');
                pgModal.classList.add('flex');
                var kartuModal = pgModal.querySelector('.overflow-y-auto');
                if (kartuModal) kartuModal.scrollTop = 0;
            })
            .catch(function () {
                alert('Gagal memuat data penggajian.');
            })
            .finally(function () {
                pgState.loading = false;
                if (trigger && teksAsli !== null) {
                    trigger.disabled = false;
                    trigger.textContent = teksAsli;
                }
            });
    }

    function tutupPgModal() {
        if (!pgModal) return;
        pgModal.classList.add('hidden');
        pgModal.classList.remove('flex');
        pgState.form = null;
        pgState.id = null;
        pgState.mode = null;
        pgSetError('');
    }

    function pgKumpulkanItems() {
        var items = [];
        document.querySelectorAll('#pgTbodyPembayaran tr, #pgTbodyPengurangan tr').forEach(function (row) {
            var kategori = row.querySelector('.pg-kategori').value;
            var nominal = pgParseAngka(row.querySelector('.pg-nominal').value);
            var keterangan = row.querySelector('.pg-keterangan').value.trim();
            if (!nominal && !keterangan) return;
            items.push({
                tipe: row.dataset.tipe,
                kategori: kategori,
                nominal: nominal,
                keterangan: keterangan
            });
        });
        return items;
    }

    function pgSimpan() {
        if (pgState.processing || !pgState.id || !pgState.form) return;

        var items = pgKumpulkanItems();

        var kosongWajib = items.some(function (it) {
            return pgWajibKeterangan(it.kategori) && !it.keterangan;
        });
        if (kosongWajib) {
            pgSetError('Keterangan wajib diisi untuk kategori Komisi Project, Insentif, Bonus, dan Custom.');
            return;
        }

        var payload = { items: items };
        var potonganInput = byId('pgPotonganKehadiran');
        if (potonganInput) {
            payload.potongan_kehadiran = pgParseAngka(potonganInput.value);
        }
        var url = '/admin/penggajian/' + pgState.id + '/rincian';
        var btn = byId('btnSimpanRincian');

        if (pgState.mode === 'koreksi') {
            var alasan = byId('pgAlasan').value.trim();
            if (!alasan) {
                pgSetError('Alasan koreksi wajib diisi.');
                return;
            }
            payload.alasan = alasan;
            url = '/admin/penggajian/' + pgState.id + '/koreksi';
            btn = byId('btnSimpanKoreksi');
        } else if (pgState.mode === 'bayar') {
            url = '/admin/penggajian/' + pgState.id + '/bayar';
            btn = byId('btnProsesBayar');
        }

        pgSetError('');
        pgState.processing = true;
        var teksTombol = btn ? btn.textContent : null;
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Memproses\u2026';
        }

        fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        })
            .then(function (res) {
                return res.json().catch(function () {
                    return { message: 'Terjadi kesalahan pada server.' };
                }).then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (hasil) {
                if (!hasil.ok) {
                    var errors = hasil.data.errors || {};
                    var pesan = hasil.data.message || 'Gagal menyimpan.';
                    Object.keys(errors).forEach(function (k) {
                        if (errors[k] && errors[k].length) pesan = errors[k][0];
                    });
                    pgSetError(pesan);
                    return;
                }
                alert(hasil.data.message || 'Berhasil disimpan.');
                var p = pgState.form.penggajian.periode.split('-');
                window.location.href = urlDenganPeriode(p[1], p[0]);
            })
            .catch(function () {
                pgSetError('Terjadi kesalahan pada server.');
            })
            .then(function () {
                pgState.processing = false;
                if (btn) {
                    btn.disabled = false;
                    if (teksTombol !== null) btn.textContent = teksTombol;
                }
            });
    }

    // ---------- Penggajian: konfirmasi sahkan (modal kustom) ----------
    var sahkanModal = document.getElementById('konfirmasiSahkanModal');
    var pgSahkanMenunggu = null;

    function openKonfirmasiSahkan(d) {
        if (!sahkanModal || !d) return;
        byId('konfirmasiSahkanNama').textContent = d.nama || '-';
        byId('konfirmasiSahkanPeriode').textContent = d.periode || '-';
        byId('konfirmasiSahkanBersih').textContent = 'Rp ' + (d.bersih || '0');
        sahkanModal.classList.remove('hidden');
        sahkanModal.classList.add('flex');
    }

    function closeKonfirmasiSahkan() {
        if (!sahkanModal) return;
        sahkanModal.classList.add('hidden');
        sahkanModal.classList.remove('flex');
        pgSahkanMenunggu = null;
    }

    function pgJalankanSahkan(id) {
        var btn = byId('btnKonfirmasiSahkan');
        if (btn) { btn.disabled = true; btn.textContent = 'Menyimpan...'; }

        fetch('/admin/penggajian/' + id + '/sahkan', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(function (res) {
                return res.json().catch(function () { return { message: 'Terjadi kesalahan pada server.' }; })
                    .then(function (data) { return { ok: res.ok, data: data }; });
            })
            .then(function (hasil) {
                closeKonfirmasiSahkan();
                alert(hasil.data.message || (hasil.ok ? 'Berhasil disahkan.' : 'Gagal mengesahkan.'));
                if (hasil.ok) window.location.reload();
            })
            .catch(function () {
                closeKonfirmasiSahkan();
                alert('Terjadi kesalahan pada server.');
            })
            .then(function () {
                if (btn) { btn.disabled = false; btn.textContent = 'Ya, Sahkan'; }
            });
    }

    function pgSahkan(d) {
        if (!d || !d.id) return;
        pgSahkanMenunggu = d.id;
        openKonfirmasiSahkan(d);
    }

    if (sahkanModal) {
        sahkanModal.addEventListener('click', function (e) {
            if (e.target.closest('[data-close-konfirmasi-sahkan-modal]')) {
                closeKonfirmasiSahkan();
            }
        });
        var btnKonfirmasiSahkan = byId('btnKonfirmasiSahkan');
        if (btnKonfirmasiSahkan) {
            btnKonfirmasiSahkan.addEventListener('click', function () {
                if (!pgSahkanMenunggu) return;
                pgJalankanSahkan(pgSahkanMenunggu);
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !sahkanModal.classList.contains('hidden')) {
                closeKonfirmasiSahkan();
            }
        });
    }

    document.addEventListener('click', function (e) {
        var kelola = e.target.closest('.btn-kelola-gaji');
        if (kelola && kelola.dataset.id) { tutupAksiMenu(); bukaPgModal(kelola.dataset.id, 'edit', kelola); return; }

        var koreksi = e.target.closest('.btn-koreksi-gaji');
        if (koreksi && koreksi.dataset.id) { tutupAksiMenu(); bukaPgModal(koreksi.dataset.id, 'koreksi', koreksi); return; }

        var bayar = e.target.closest('.btn-bayar-gaji');
        if (bayar && bayar.dataset.id) { tutupAksiMenu(); bukaPgModal(bayar.dataset.id, 'bayar', bayar); return; }

        var sahkanBtn = e.target.closest('.btn-sahkan-gaji');
        if (sahkanBtn && sahkanBtn.dataset.id) {
            tutupAksiMenu();
            pgSahkan({
                id: sahkanBtn.dataset.id,
                nama: sahkanBtn.dataset.nama,
                periode: sahkanBtn.dataset.periode,
                bersih: sahkanBtn.dataset.bersih
            });
            return;
        }

        var cetakStruk = e.target.closest('.btn-cetak-struk');
        if (cetakStruk) { tutupAksiMenu(); return; }

        var tambah = e.target.closest('#btnTambahPembayaran, #btnTambahPengurangan');
        if (tambah && pgState.form) {
            var tipe = tambah.id === 'btnTambahPengurangan' ? 'pengurangan' : 'pembayaran';
            var tbody = tipe === 'pengurangan' ? byId('pgTbodyPengurangan') : byId('pgTbodyPembayaran');
            var row = pgBuatBaris(tipe, null);
            if (row && tbody) {
                tbody.appendChild(row);
                pgHitung();
            }
            return;
        }

        var hapus = e.target.closest('.pg-hapus');
        if (hapus) {
            var tr = hapus.closest('tr');
            if (tr) {
                tr.remove();
                pgHitung();
            }
            return;
        }

        if (e.target.closest('#btnSimpanRincian, #btnSimpanKoreksi, #btnProsesBayar')) { pgSimpan(); return; }

        if (e.target.closest('[data-close-penggajian-rincian-modal]')) { tutupPgModal(); }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('pg-kategori')) {
            pgTerapkanKeterangan(e.target.closest('tr'));
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList && e.target.classList.contains('pg-rupiah')) {
            pgFormatInput(e.target);
            pgHitung();
            return;
        }
        if (e.target.id === 'pgAlasan') {
            var btnKoreksi = byId('btnSimpanKoreksi');
            if (btnKoreksi) btnKoreksi.disabled = e.target.value.trim() === '';
        }
    });

    if (pgModal) {
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !pgModal.classList.contains('hidden')) tutupPgModal();
        });
    }
})();
