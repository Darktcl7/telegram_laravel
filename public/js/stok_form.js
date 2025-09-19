document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('stok-form');
    const statusSelect = document.getElementById('status');
    const produkArea = document.getElementById('produk-area');
    const produkWrapper = document.getElementById('produk-wrapper');
    const addProdukBtn = document.getElementById('add-produk-btn');
    const submitBtn = document.getElementById('submit-btn');

    let produkIndex = 1; // next index for new produk blocks
    let activeImeiInput = null;
    let html5QrCode = null;
    let isScanning = false;

    // disable submit button on submit to prevent double submit
    if (form) {
        form.addEventListener('submit', function() {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Menyimpan...';
            }
        });
    }

    // status change: show/hide produk area and enable/disable inputs
    statusSelect.addEventListener('change', function () {
        if (this.value === 'ada') {
            produkArea.style.display = 'block';
            enableProdukInputs(true);
        } else {
            produkArea.style.display = 'none';
            enableProdukInputs(false);
        }
    });

    function enableProdukInputs(flag) {
        // set disabled property for all inputs/selects in produk area
        produkWrapper.querySelectorAll('input, select, button').forEach(el => {
            // keep submit and add-produk btn unaffected (not inside produkWrapper)
            if (el.closest('#produk-wrapper')) {
                if (el.classList.contains('scan-btn') || el.classList.contains('add-imei-btn') || el.classList.contains('remove-imei-btn') || el.classList.contains('remove-produk-btn')) {
                    el.disabled = !flag;
                } else {
                    el.disabled = !flag;
                }
            }
        });
        // add-produk button
        document.getElementById('add-produk-btn').disabled = !flag;
    }

    // event delegation for produk wrapper (add/remove imei, scan, remove produk)
    produkWrapper.addEventListener('click', function (e) {
        const addImei = e.target.closest('.add-imei-btn');
        const removeImei = e.target.closest('.remove-imei-btn');
        const scanBtn = e.target.closest('.scan-btn');
        const removeProdukBtn = e.target.closest('.remove-produk-btn');

        if (addImei) {
            const block = addImei.closest('.produk-block');
            const idx = block.getAttribute('data-index');
            const imeiContainer = block.querySelector('.imei-repeater');
            imeiContainer.appendChild(createImeiRow(idx));
        }

        if (removeImei) {
            const row = removeImei.closest('.imei-row');
            if (row) row.remove();
        }

        if (scanBtn) {
            activeImeiInput = scanBtn.closest('.imei-row').querySelector('.imei-input');
            launchScannerForInput(activeImeiInput);
        }

        if (removeProdukBtn) {
            const block = removeProdukBtn.closest('.produk-block');
            if (block) block.remove();
            refreshProdukIndices();
        }
    });

    // add new produk block
    addProdukBtn.addEventListener('click', function () {
        produkWrapper.appendChild(createProdukBlock(produkIndex));
        produkIndex++;
        refreshProdukIndices();
    });

    // create product block element
    function createProdukBlock(index) {
        const div = document.createElement('div');
        div.className = 'produk-block border rounded p-3 mb-3';
        div.setAttribute('data-index', index);

        // build produk select options from server-rendered template
        // To keep options consistent, copy options from the first block's select
        const firstSelect = document.querySelector('.produk-select');
        let optionsHtml = firstSelect ? firstSelect.innerHTML : '<option value="">-- Pilih Produk --</option>';

        div.innerHTML = `
            <div class="produk-header mb-2">
                <div>
                    <span class="produk-index">#</span>
                    <strong>Barang</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-produk-btn">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label">Produk</label>
                <select name="produk[${index}][produk_id]" class="form-select produk-select" required>
                    ${optionsHtml}
                </select>
            </div>

            <div class="mb-2">
                <label class="form-label">IMEI</label>
                <div class="imei-repeater">
                    <div class="input-group mb-2 imei-row">
                        <input type="text" name="produk[${index}][imei][]" class="form-control imei-input" placeholder="Scan IMEI..." readonly>
                        <button type="button" class="btn btn-outline-secondary scan-btn" title="Scan IMEI"><i class="fas fa-camera"></i></button>
                        <button type="button" class="btn btn-outline-success add-imei-btn" title="Tambah IMEI"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>
        `;

        return div;
    }

    // create imei row for a produk index
    function createImeiRow(index) {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 imei-row';
        div.innerHTML = `
            <input type="text" name="produk[${index}][imei][]" class="form-control imei-input" placeholder="Scan IMEI..." readonly>
            <button type="button" class="btn btn-outline-secondary scan-btn" title="Scan IMEI"><i class="fas fa-camera"></i></button>
            <button type="button" class="btn btn-outline-danger remove-imei-btn" title="Hapus IMEI"><i class="fas fa-minus"></i></button>
        `;
        return div;
    }

    // refresh produk numbering / input names are already set when created
    function refreshProdukIndices() {
        const blocks = produkWrapper.querySelectorAll('.produk-block');
        blocks.forEach((b, i) => {
            b.setAttribute('data-index', i);
            const idxLabel = b.querySelector('.produk-index');
            if (idxLabel) idxLabel.textContent = `#${i + 1}`;
            // update names for produk select and imei rows to match index if necessary
            const select = b.querySelector('.produk-select');
            if (select) {
                select.name = `produk[${i}][produk_id]`;
            }
            const imeiRows = b.querySelectorAll('.imei-row');
            imeiRows.forEach(row => {
                const input = row.querySelector('.imei-input');
                if (input) input.name = `produk[${i}][imei][]`;
            });

            // show remove button for blocks except the first
            const remBtn = b.querySelector('.remove-produk-btn');
            if (remBtn) remBtn.style.display = i === 0 ? 'none' : 'inline-block';
        });

        // update produkIndex to next after last
        produkIndex = blocks.length;
    }

    // check if IMEI is duplicate among all imei inputs
    function isImeiDuplicate(value) {
        if (!value) return false;
        const inputs = document.querySelectorAll('.imei-input');
        let count = 0;
        for (let input of inputs) {
            if (input.value && input.value.trim() === value.trim()) {
                count++;
            }
            if (count > 0) {
                // when checking for new scan, we want to allow current active input to be set even if it has same value
                // but here we'll just prevent duplicates existing elsewhere
            }
        }
        // We return true if value already exists in any input (excluding the currently active input if needed)
        for (let input of inputs) {
            if (input !== activeImeiInput && input.value && input.value.trim() === value.trim()) {
                return true;
            }
        }
        return false;
    }

    // Scanner handling (Telegram WebApp or html5-qrcode fallback)
    function launchScannerForInput(inputEl) {
        activeImeiInput = inputEl;
        // first, try Telegram WebApp popup (if running inside Telegram WebApp)
        if (window.Telegram && window.Telegram.WebApp && typeof Telegram.WebApp.openScanQrPopup === 'function') {
            try {
                Telegram.WebApp.openScanQrPopup({ text: "Arahkan kamera ke barcode IMEI" }, function (data) {
                    if (data && activeImeiInput) {
                        if (isImeiDuplicate(data)) {
                            alert('IMEI ini sudah di-scan pada baris lain.');
                        }
                    } else {
                        activeImeiInput.value = data;
                    }
                });
                return;
            } catch (err) {
                console.warn('Telegram scan popup failed, fallback to html5 scanner', err);
            }
        }
        // fallback to html5 scanner
        startHtml5Scanner();
    }

    function onScanSuccess(decodedText) {
        if (decodedText && activeImeiInput) {
            if (isImeiDuplicate(decodedText)) {
                alert('IMEI ini sudah di-scan pada baris lain.');
            } else {
                activeImeiInput.value = decodedText;
            }
        }
        stopHtml5Scanner();
    }

    function startHtml5Scanner() {
        document.getElementById("scanner-overlay").style.display = "flex";
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }
        const config = { fps: 10, qrbox: { width: 300, height: 300 } };
        if (!isScanning) {
            isScanning = true;
            html5QrCode.start({ facingMode: "environment" }, config, (decodedText, decodedResult) => {
                onScanSuccess(decodedText);
            }).catch(err => {
                isScanning = false;
                alert("Gagal memulai kamera: " + err);
                stopHtml5Scanner();
            });
        }
    }

    function stopHtml5Scanner() {
        const overlay = document.getElementById("scanner-overlay");
        if (html5QrCode && isScanning) {
            html5QrCode.stop().then(() => {
                isScanning = false;
                if (overlay) overlay.style.display = "none";
            }).catch(err => {
                console.error("Gagal stop scanner", err);
                isScanning = false;
                if (overlay) overlay.style.display = "none";
            });
        } else if (overlay) {
            overlay.style.display = "none";
            isScanning = false;
        }
    }

    document.getElementById("close-scanner-btn").addEventListener("click", stopHtml5Scanner);

    // Initialize: hide produk area until status 'ada'
    enableProdukInputs(false);

    // initial refresh indices
    refreshProdukIndices();

    // (Optional) Prevent form submit if any IMEI empty or duplicate found client-side
    form.addEventListener('submit', function (e) {
        if (statusSelect.value === 'ada') {
            // check all produk blocks have produk selected and at least one imei non-empty
            const blocks = produkWrapper.querySelectorAll('.produk-block');
            let errorMsg = null;
            let totalImeis = 0;
            blocks.forEach((b, i) => {
                const prodSelect = b.querySelector('.produk-select');
                if (!prodSelect || !prodSelect.value) {
                    errorMsg = 'Silakan pilih produk untuk setiap barang baru.';
                }
                const imeiInputs = b.querySelectorAll('.imei-input');
                let blockHasImei = false;
                imeiInputs.forEach(inp => {
                    if (inp.value && inp.value.trim() !== '') {
                        blockHasImei = true;
                        totalImeis++;
                    }
                });
                if (!blockHasImei) {
                    errorMsg = 'Setiap barang harus memiliki minimal 1 IMEI.';
                }
            });

            // check duplicates
            const seen = {};
            document.querySelectorAll('.imei-input').forEach(inp => {
                const v = inp.value && inp.value.trim();
                if (!v) return;
                if (seen[v]) {
                    errorMsg = `IMEI duplikat ditemukan: ${v}`;
                } else {
                    seen[v] = true;
                }
            });

            if (errorMsg) {
                e.preventDefault();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan';
                }
                alert(errorMsg);
                return false;
            }
            if (totalImeis === 0) {
                e.preventDefault();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Simpan';
                }
                alert('Tidak ada IMEI ditemukan. Tambahkan minimal 1 IMEI.');
                return false;
            }
        }
        // else status = 'tidak' valid
    });
});