@extends('layouts.app')
@section('title', 'Dashboard Pedagang - Pantau Pangan')
@push('styles')
<style>
.autocomplete-wrap{position:relative;}
.autocomplete-list{position:absolute;top:100%;left:0;right:0;max-height:200px;overflow-y:auto;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;z-index:50;display:none;box-shadow:var(--shadow);}
.autocomplete-list.show{display:block;}
.ac-item{padding:10px 14px;cursor:pointer;font-size:0.875rem;border-bottom:1px solid var(--border);transition:var(--transition);}
.ac-item:hover,.ac-item.selected{background:var(--primary-50);color:var(--primary);}
.ac-item:last-child{border-bottom:none;}
.ac-new{color:var(--primary);font-weight:600;}
</style>
@endpush
@section('content')
<div class="container">
    <div class="page-header"><h1 class="page-title">🏪 Dashboard Pedagang</h1><p class="page-subtitle" id="merchant-status">Memuat...</p></div>

    {{-- WAITING VERIFICATION BANNER --}}
    <div id="waiting-banner" style="display:none;" class="card mb-3">
        <div style="display:flex;align-items:center;gap:16px;padding:24px;border-left:4px solid var(--warning);">
            <div style="font-size:3rem;">⏳</div>
            <div><h3 style="font-weight:700;color:var(--accent-dark);">Menunggu Verifikasi Admin</h3>
            <p class="text-muted">Akun pedagang Anda sedang dalam proses verifikasi.</p></div>
        </div>
    </div>

    <div id="main-content" style="display:none;">
    <div class="grid" style="grid-template-columns:2fr 1fr;gap:32px;">
    <div>
        {{-- SECTION 1: Tambah Produk & Harga --}}
        <div class="card mb-3">
            <h3 style="font-weight:700;margin-bottom:4px;">📦 Tambah Produk & Harga</h3>
            <p class="text-sm text-muted mb-2">Pilih produk yang sudah ada atau ketik nama baru</p>
            <form id="add-product-form">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Kategori</label>
                        <select class="form-select" id="ap-category" required><option value="">Pilih Kategori</option></select>
                    </div>
                    <div class="form-group"><label class="form-label">Satuan</label>
                        <select class="form-select" id="ap-unit" required><option value="kg">Kilogram (kg)</option><option value="liter">Liter</option><option value="butir">Butir</option><option value="ikat">Ikat</option></select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Nama Produk</label>
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-input" id="ap-name" placeholder="Ketik untuk cari atau tambah baru..." autocomplete="off" required>
                            <div class="autocomplete-list" id="ap-ac-list"></div>
                        </div>
                        <input type="hidden" id="ap-product-id" value="">
                    </div>
                    <div class="form-group"><label class="form-label">Toko</label>
                        <select class="form-select" id="ap-store" required><option value="">Pilih Toko</option></select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Harga (Rp)</label>
                        <input type="text" class="form-input" id="ap-price" placeholder="Rp 0" required>
                    </div>
                    <div class="form-group"><label class="form-label">Status Stok</label>
                        <select class="form-select" id="ap-stock"><option value="available">Tersedia</option><option value="scarce">Langka</option><option value="out_of_stock">Habis</option></select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="ap-btn">➕ Simpan</button>
            </form>
        </div>

        {{-- SECTION 2: Daftar Harga Barang --}}
        <div class="card mb-3" style="padding:20px;">
            <h3 style="font-weight:700;margin-bottom:12px;">📋 Daftar Harga Barang</h3>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
                <div class="search-bar" style="flex:1;min-width:200px;">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="price-search" placeholder="Cari produk...">
                </div>
                <select class="form-select" id="price-category-filter" style="width:auto;min-width:160px;">
                    <option value="">Semua Kategori</option>
                </select>
            </div>
            <div class="table-wrap">
                <table class="table"><thead><tr><th>Produk</th><th>Toko</th><th>Harga</th><th>Stok</th><th>Update</th><th>Aksi</th></tr></thead>
                <tbody id="prices-body"><tr><td colspan="6" class="text-center"><div class="spinner"></div></td></tr></tbody></table>
            </div>
        </div>

        {{-- SECTION 3: Data Anomali --}}
        <div class="card mb-3" style="padding:20px;">
            <h3 style="font-weight:700;margin-bottom:4px;">⚠️ Data Anomali</h3>
            <p class="text-sm text-muted mb-2">Harga yang ditandai anomali oleh sistem, menunggu review admin</p>
            <div id="anomaly-list"><div class="spinner"></div></div>
        </div>
    </div>

    <div>
        <div class="card mb-3" style="padding:20px;"><h3 style="font-weight:700;margin-bottom:12px;">📊 Statistik</h3><div id="merchant-stats"><div class="spinner"></div></div></div>
        <div class="card mb-3" style="padding:20px;">
            <h3 style="font-weight:700;margin-bottom:12px;">🏪 Toko Saya</h3>
            <div id="my-stores"><div class="spinner"></div></div>
            <button class="btn btn-secondary btn-sm mt-2" style="width:100%;" onclick="showAddStoreModal()">➕ Ajukan Toko Baru</button>
            <button class="btn btn-ghost btn-sm mt-1" style="width:100%;" onclick="showEditProfileModal()">✏️ Edit Profil Toko Utama</button>
        </div>
    </div>
    </div>
    </div>
</div>

{{-- Add Store Modal --}}
<div class="modal-overlay" id="store-modal">
    <div class="modal">
        <div class="flex-between mb-3"><h3 class="modal-title">🏪 Ajukan Penambahan Toko</h3><button class="btn btn-ghost btn-sm" onclick="closeStoreModal()">✕</button></div>
        <div id="store-region-notice" class="card mb-2" style="padding:12px;background:var(--primary-50);display:none;"><p class="text-sm" style="color:var(--primary-dark);">ℹ️ Toko baru harus di daerah: <strong id="store-region-name"></strong></p></div>
        <form id="add-store-form">
            <div class="form-group"><label class="form-label">Nama Toko</label><input type="text" class="form-input" id="as-name" required></div>
            <div class="form-group"><label class="form-label">Alamat Toko</label><input type="text" class="form-input" id="as-address"></div>
            <div class="form-group"><label class="form-label">Pasar</label><select class="form-select" id="as-market" required><option value="">Pilih Pasar</option></select></div>
            <div class="form-group">
                <label class="form-label">Foto Toko <span class="text-error">*</span></label>
                <input type="file" class="form-input" id="as-photo" accept="image/*" required>
                <p class="text-sm text-muted mt-1" style="font-size: 0.75rem;">Maksimal 2MB (JPG, PNG, WebP)</p>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;" id="as-btn">Ajukan Toko</button>
        </form>
    </div>
</div>

{{-- Edit Price Modal --}}
<div class="modal-overlay" id="edit-modal">
    <div class="modal">
        <div class="flex-between mb-3"><h3 class="modal-title">✏️ Edit Harga & Stok</h3><button class="btn btn-ghost btn-sm" onclick="closeEditModal()">✕</button></div>
        <p id="edit-product-name" style="font-weight:700;margin-bottom:16px;"></p>
        <form id="edit-price-form">
            <input type="hidden" id="edit-price-id">
            <div class="form-group"><label class="form-label">Harga (Rp)</label><input type="text" class="form-input" id="edit-price-val"></div>
            <div class="form-group"><label class="form-label">Status Stok</label>
                <select class="form-select" id="edit-stock-val"><option value="available">Tersedia</option><option value="scarce">Langka</option><option value="out_of_stock">Habis</option></select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Perubahan</button>
        </form>
    </div>
</div>

{{-- Edit Profile Modal --}}
<div class="modal-overlay" id="profile-modal">
    <div class="modal" style="border-radius: 20px; box-shadow: var(--shadow-lg); padding: 32px; border: 1px solid var(--border); max-width: 480px; width: 100%;">
        <div class="flex-between mb-3" style="border-bottom: 1px solid var(--border); padding-bottom: 16px;">
            <h3 class="modal-title" style="font-weight: 800; font-size: 1.25rem; color: var(--text); display: flex; align-items: center; gap: 8px; margin: 0;">
                <span>✏️</span> Edit Profil Toko Utama
            </h3>
            <button class="btn btn-ghost btn-sm" style="border-radius: 50%; width: 32px; height: 32px; padding: 0;" onclick="closeProfileModal()">✕</button>
        </div>
        <form id="edit-profile-form">
            <div class="form-group">
                <label class="form-label" style="font-weight:600; color:var(--text);">Nama Toko Utama</label>
                <input type="text" class="form-input" id="ep-store-name" style="border-radius: 8px; border: 1.5px solid var(--border);" required>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight:600; color:var(--text);">Alamat Toko Utama</label>
                <input type="text" class="form-input" id="ep-store-address" style="border-radius: 8px; border: 1.5px solid var(--border);">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight:600; color:var(--text);">Daerah Utama</label>
                <select class="form-select" id="ep-region" style="border-radius: 8px; border: 1.5px solid var(--border);" required>
                    <option value="">Pilih Daerah</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight:600; color:var(--text);">Pasar Utama</label>
                <select class="form-select" id="ep-market" style="border-radius: 8px; border: 1.5px solid var(--border);" required disabled>
                    <option value="">Pilih Daerah terlebih dahulu</option>
                </select>
                <div id="ep-region-warning" style="margin-top: 8px; display: none; gap: 8px; align-items: flex-start; background: rgba(239, 68, 68, 0.05); padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.2);">
                    <span style="font-size: 1rem;">⚠️</span>
                    <p style="font-size: 0.75rem; color: var(--danger); margin: 0; line-height: 1.45; font-weight: 500;">
                        <strong>Penting:</strong> Mengubah daerah operasional utama memerlukan persetujuan ulang dari Admin, dan hak input harga Anda akan dibekukan sementara (pending) demi validitas data.
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-weight:600; color:var(--text);">Deskripsi Toko</label>
                <textarea class="form-textarea" id="ep-description" rows="3" style="border-radius: 8px; border: 1.5px solid var(--border);"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; border-radius:10px; font-weight:700; padding:12px;" id="ep-btn">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let merchantStores=[], merchantRegionId=null, allCategoryProducts={}, selectedProductId=null, currentMerchantProfile=null;

function formatInputRupiah(el){let v=el.value.replace(/\D/g,'');el.value=v?'Rp '+parseInt(v).toLocaleString('id-ID'):'';}
function parseRupiah(s){return parseInt((s||'0').replace(/\D/g,''))||0;}
document.getElementById('ap-price').addEventListener('input',function(){formatInputRupiah(this);});
document.getElementById('edit-price-val').addEventListener('input',function(){formatInputRupiah(this);});

document.addEventListener('DOMContentLoaded', async()=>{
    if(!Auth.requireAuth()) return;
    const user=Auth.getUser();
    if(user?.role!=='pedagang'&&user?.role!=='admin'){window.location.href='/dashboard';return;}

    try{
        const me=await api('/auth/me');
        const mp=me.user?.merchant_profile;
        if(mp){
            currentMerchantProfile=mp;
            if(mp.status==='approved'){
                document.getElementById('merchant-status').innerHTML='<span class="badge badge-success">✅ Terverifikasi</span> Anda dapat menginput harga';
                document.getElementById('main-content').style.display='block';
            } else if(mp.status==='pending'){
                document.getElementById('merchant-status').innerHTML='<span class="badge badge-warning">⏳ Menunggu Verifikasi</span>';
                document.getElementById('waiting-banner').style.display='block';
                return;
            } else {
                document.getElementById('merchant-status').innerHTML='<span class="badge badge-danger">❌ Ditolak</span>';
                return;
            }
        }
    }catch(e){}

    // Load categories for form + filter
    try{
        const cats=await api('/categories');
        const sel=document.getElementById('ap-category');
        const filterSel=document.getElementById('price-category-filter');
        cats.categories.forEach(c=>{
            sel.innerHTML+=`<option value="${c.id}">${c.icon} ${c.name}</option>`;
            filterSel.innerHTML+=`<option value="${c.id}">${c.icon} ${c.name}</option>`;
        });
    }catch(e){}

    await loadStores();
    loadPrices();
    loadStats();
    loadAnomalies();
});

// ===== AUTOCOMPLETE PRODUCT =====
const apName=document.getElementById('ap-name');
const apAcList=document.getElementById('ap-ac-list');
const apCategory=document.getElementById('ap-category');
const apUnit=document.getElementById('ap-unit');

apCategory.addEventListener('change', async function(){
    selectedProductId=null;
    document.getElementById('ap-product-id').value='';
    apName.value='';
    apUnit.disabled=false;
    const catId=this.value;
    if(!catId){allCategoryProducts={};return;}
    try{
        const data=await api('/products?category='+catId+'&per_page=100');
        // Store products by category ID - use slug param won't work, need to match by category
        const cats=await api('/categories');
        const cat=cats.categories.find(c=>c.id==catId);
        if(cat){
            const prods=await api('/products?category='+cat.slug+'&per_page=100');
            allCategoryProducts[catId]=(prods.data||[]);
        }
    }catch(e){allCategoryProducts[catId]=[];}
});

apName.addEventListener('input', function(){
    const q=this.value.trim().toLowerCase();
    const catId=apCategory.value;
    const products=allCategoryProducts[catId]||[];
    selectedProductId=null;
    document.getElementById('ap-product-id').value='';
    apUnit.disabled=false; // Enable unit when typing new

    if(q.length<1){apAcList.classList.remove('show');return;}

    const filtered=products.filter(p=>p.name.toLowerCase().includes(q));
    let html='';
    filtered.forEach(p=>{
        html+=`<div class="ac-item" data-id="${p.id}" data-name="${p.name}" data-unit="${p.unit}">${p.name} <span class="text-sm text-muted">(${p.unit})</span></div>`;
    });
    // Option to add new
    const exactMatch=products.some(p=>p.name.toLowerCase()===q);
    if(!exactMatch && q.length>=2){
        html+=`<div class="ac-item ac-new" data-id="new" data-name="${this.value.trim()}">➕ Tambah baru: "${this.value.trim()}"</div>`;
    }
    apAcList.innerHTML=html||'<div class="ac-item" style="color:var(--text-light)">Tidak ditemukan. Ketik lebih untuk tambah baru.</div>';
    apAcList.classList.add('show');
});

apAcList.addEventListener('click', function(e){
    const item=e.target.closest('.ac-item');
    if(!item) return;
    const id=item.dataset.id;
    const name=item.dataset.name;
    apName.value=name;
    if(id==='new'){
        selectedProductId=null;
        document.getElementById('ap-product-id').value='';
        apUnit.disabled=false;
    } else {
        selectedProductId=parseInt(id);
        document.getElementById('ap-product-id').value=id;
        if(item.dataset.unit) {
            apUnit.value=item.dataset.unit;
            apUnit.disabled=true; // Disable unit change for existing product
        }
    }
    apAcList.classList.remove('show');
});

apName.addEventListener('focus', function(){if(this.value.length>=1) this.dispatchEvent(new Event('input'));});
document.addEventListener('click',e=>{if(!e.target.closest('.autocomplete-wrap'))apAcList.classList.remove('show');});

// ===== STORES =====
async function loadStores(){
    try{
        const data=await api('/merchant/stores');
        merchantStores=data.stores||[];
        const sel=document.getElementById('ap-store');
        sel.innerHTML='<option value="">Pilih Toko</option>';
        merchantStores.forEach(s=>{
            if(s.status==='approved') sel.innerHTML+=`<option value="${s.id}" data-market="${s.market_id}">${s.store_name} (${s.market?.name||''})</option>`;
        });
        if(merchantStores.length>0) merchantRegionId=merchantStores[0].region_id;
        const storesDiv=document.getElementById('my-stores');
        storesDiv.innerHTML=merchantStores.map(s=>`
            <div style="padding:8px 0;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
                ${s.shop_photo ? `<img src="/storage/${s.shop_photo}" alt="Foto" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">` : `<div style="width:40px;height:40px;background:var(--bg-color);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--text-muted);">🏪</div>`}
                <div style="flex:1;"><div style="font-weight:600;font-size:0.9rem;">${s.store_name}</div>
                <div class="text-sm text-muted">${s.market?.name||''}</div></div>
                <div>${s.status==='approved'?'<span class="badge badge-success">Aktif</span>':s.status==='pending'?'<span class="badge badge-warning">Menunggu</span>':'<span class="badge badge-danger">Ditolak</span>'}</div>
            </div>`).join('')||'<p class="text-muted text-sm">Belum ada toko</p>';
    }catch(e){}
}

// ===== PRICE LIST WITH FILTERS =====
let searchTimer;
document.getElementById('price-search').addEventListener('input',function(){clearTimeout(searchTimer);searchTimer=setTimeout(loadPrices,400);});
document.getElementById('price-category-filter').addEventListener('change',loadPrices);

async function loadPrices(){
    const tbody=document.getElementById('prices-body');
    tbody.innerHTML='<tr><td colspan="6" class="text-center"><div class="spinner"></div></td></tr>';
    let params='?per_page=50';
    const search=document.getElementById('price-search').value;
    const cat=document.getElementById('price-category-filter').value;
    if(search) params+=`&search=${search}`;
    if(cat) params+=`&category=${cat}`;
    try{
        const d=await api('/merchant/history'+params);
        const items=d.data||[];
        if(!items.length){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted" style="padding:32px;">Tidak ada data ditemukan</td></tr>';return;}
        // Build store name map from merchantStores for fallback
        const storeByMarket={};
        merchantStores.forEach(s=>{if(s.status==='approved')storeByMarket[s.market_id]=s.store_name;});
        tbody.innerHTML=items.map(p=>{
            const storeName=p.store?.store_name||storeByMarket[p.market_id]||p.market?.name||'-';
            const unitText=p.product?.unit ? ` / ${p.product.unit}` : '';
            return `<tr>
                <td style="font-weight:600">${p.product?.name||'-'}</td>
                <td class="text-sm">${storeName}</td>
                <td style="font-weight:700;color:var(--primary);">${formatRupiah(p.price)}<span class="text-muted text-sm" style="font-weight:normal">${unitText}</span></td>
                <td>${stockLabel(p.stock_status)}</td>
                <td class="text-sm text-muted">${timeAgo(p.created_at)}</td>
                <td><button class="btn btn-secondary btn-sm" onclick="openEditModal(${p.id},'${(p.product?.name||'').replace(/'/g,"\\'")}',${p.price},'${p.stock_status}')">✏️</button></td>
            </tr>`;
        }).join('');
    }catch(e){tbody.innerHTML='<tr><td colspan="6" class="text-center text-muted">Gagal memuat</td></tr>';}
}

// ===== ANOMALY LIST =====
async function loadAnomalies(){
    const el=document.getElementById('anomaly-list');
    try{
        const d=await api('/merchant/suspicious');
        const items=d.data||[];
        if(!items.length){el.innerHTML='<div class="text-center" style="padding:24px;"><div style="font-size:2rem;margin-bottom:8px;">✅</div><p class="text-muted text-sm">Tidak ada data anomali saat ini</p></div>';return;}
        el.innerHTML='<div class="table-wrap"><table class="table"><thead><tr><th>Produk</th><th>Harga</th><th>Toko</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>'+
        items.map(p=>{
            const storeName=merchantStores.find(s=>s.market_id===p.market_id)?.store_name||p.market?.name||'-';
            return `<tr style="background:rgba(239,68,68,0.04);">
                <td style="font-weight:600">${p.product?.name||'-'}</td>
                <td style="font-weight:700;color:var(--danger);">${formatRupiah(p.price)}</td>
                <td class="text-sm">${storeName}</td>
                <td class="text-sm text-muted">${timeAgo(p.created_at)}</td>
                <td><span class="badge badge-warning">⏳ Menunggu Review</span></td>
            </tr>`;
        }).join('')+'</tbody></table></div>';
    }catch(e){el.innerHTML='<p class="text-muted text-sm">Gagal memuat</p>';}
}

// ===== STATS =====
async function loadStats(){
    try{
        const d=await api('/merchant/stats');
        document.getElementById('merchant-stats').innerHTML=`
            <div class="stat-card mb-2"><div class="stat-value">${d.total_contributions}</div><div class="stat-label">Total Kontribusi</div></div>
            <div class="stat-card mb-2"><div class="stat-value">${d.valid_contributions}</div><div class="stat-label">Kontribusi Valid</div></div>
            <div class="stat-card"><div class="stat-value">${d.suspicious_count}</div><div class="stat-label">Ditandai Anomali</div></div>`;
    }catch(e){document.getElementById('merchant-stats').innerHTML='<p class="text-muted text-sm">Gagal memuat</p>';}
}

// ===== ADD PRODUCT FORM =====
document.getElementById('add-product-form').addEventListener('submit', async(e)=>{
    e.preventDefault();
    const btn=document.getElementById('ap-btn');
    btn.textContent='Memproses...';btn.disabled=true;
    const name=document.getElementById('ap-name').value.trim();
    const categoryId=parseInt(document.getElementById('ap-category').value);
    const unit=document.getElementById('ap-unit').value;
    const storeOpt=document.getElementById('ap-store');
    const storeId=storeOpt.value;
    const marketId=storeOpt.selectedOptions[0]?.dataset?.market;
    const price=parseRupiah(document.getElementById('ap-price').value);
    const stock=document.getElementById('ap-stock').value;
    const existingId=document.getElementById('ap-product-id').value;

    if(!name||!categoryId||!storeId||price<100){showToast('Lengkapi semua field','error');btn.textContent='➕ Simpan';btn.disabled=false;return;}

    try{
        let productId=existingId?parseInt(existingId):null;
        // If new product, create it first
        if(!productId){
            const prodRes=await api('/merchant/products','POST',{name,category_id:categoryId,unit});
            productId=prodRes.product.id;
        }
        // Add price
        const priceRes=await api('/merchant/prices','POST',{product_id:productId,market_id:parseInt(marketId),store_id:parseInt(storeId),price,stock_status:stock});
        showToast(priceRes.message||'Berhasil!');
        if(priceRes.is_suspicious) showToast('Harga ditandai anomali oleh sistem','warning');
        document.getElementById('add-product-form').reset();
        document.getElementById('ap-product-id').value='';
        selectedProductId=null;
        loadPrices();loadStats();loadAnomalies();
    }catch(err){
        const msg=err.data?.errors?Object.values(err.data.errors).flat().join(', '):(err.data?.message||'Gagal');
        showToast(msg,'error');
    }
    btn.textContent='➕ Simpan';btn.disabled=false;
});

// ===== EDIT MODAL =====
function openEditModal(id,name,price,stock){
    document.getElementById('edit-price-id').value=id;
    document.getElementById('edit-product-name').textContent=name;
    document.getElementById('edit-price-val').value='Rp '+parseInt(price).toLocaleString('id-ID');
    document.getElementById('edit-stock-val').value=stock;
    document.getElementById('edit-modal').classList.add('show');
}
function closeEditModal(){document.getElementById('edit-modal').classList.remove('show');}
document.getElementById('edit-price-form').addEventListener('submit',async(e)=>{
    e.preventDefault();
    const id=document.getElementById('edit-price-id').value;
    const price=parseRupiah(document.getElementById('edit-price-val').value);
    const stock=document.getElementById('edit-stock-val').value;
    try{await api(`/merchant/prices/${id}`,'PUT',{price,stock_status:stock});showToast('Harga berhasil diperbarui!');closeEditModal();loadPrices();loadAnomalies();}
    catch(err){showToast(err.data?.message||'Gagal','error');}
});

// ===== STORE MODAL =====
async function showAddStoreModal(){
    document.getElementById('store-modal').classList.add('show');
    if(merchantRegionId){
        document.getElementById('store-region-notice').style.display='block';
        try{const data=await api(`/regions/${merchantRegionId}/markets`);const sel=document.getElementById('as-market');sel.innerHTML='<option value="">Pilih Pasar</option>';data.markets.forEach(m=>{sel.innerHTML+=`<option value="${m.id}">${m.name}</option>`;});document.getElementById('store-region-name').textContent=data.region;}catch(e){}
    } else {
        try{const regions=await api('/regions');const sel=document.getElementById('as-market');sel.innerHTML='<option value="">Pilih Pasar</option>';for(const r of regions.regions){const mData=await api(`/regions/${r.id}/markets`);mData.markets.forEach(m=>{sel.innerHTML+=`<option value="${m.id}">${m.name} (${r.name})</option>`;});}}catch(e){}
    }
}
function closeStoreModal(){document.getElementById('store-modal').classList.remove('show');}
document.getElementById('add-store-form').addEventListener('submit',async(e)=>{
    e.preventDefault();const btn=document.getElementById('as-btn');btn.textContent='Memproses...';btn.disabled=true;
    
    const fileInput = document.getElementById('as-photo');
    if (!fileInput.files[0]) { showToast('Pilih foto toko', 'error'); btn.textContent='Ajukan Toko'; btn.disabled=false; return; }
    if (fileInput.files[0].size > 2 * 1024 * 1024) { showToast('Maksimal 2MB', 'error'); btn.textContent='Ajukan Toko'; btn.disabled=false; return; }

    try {
        const formData = new FormData();
        formData.append('store_name', document.getElementById('as-name').value);
        formData.append('store_address', document.getElementById('as-address').value);
        formData.append('market_id', document.getElementById('as-market').value);
        formData.append('shop_photo', fileInput.files[0]);

        const res = await fetch('/api/merchant/stores', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + Auth.getToken(), 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (!res.ok) throw { status: res.status, data };

        showToast('Pengajuan toko berhasil!');
        closeStoreModal();
        document.getElementById('add-store-form').reset();
        loadStores();
    }
    catch(err){showToast(err.data?.message||'Gagal','error');}
    btn.textContent='Ajukan Toko';btn.disabled=false;
});

// ===== PROFILE MODAL =====
function closeProfileModal() {
    document.getElementById('profile-modal').classList.remove('show');
}

async function showEditProfileModal(){
    if(!currentMerchantProfile) return;
    
    document.getElementById('profile-modal').classList.add('show');
    
    // Reset warning
    document.getElementById('ep-region-warning').style.display = 'none';
    
    // Load inputs
    document.getElementById('ep-store-name').value = currentMerchantProfile.store_name||'';
    document.getElementById('ep-store-address').value = currentMerchantProfile.store_address||'';
    document.getElementById('ep-description').value = currentMerchantProfile.description||'';
    
    const regSel = document.getElementById('ep-region');
    const marketSel = document.getElementById('ep-market');
    
    regSel.innerHTML = '<option value="">Pilih Daerah</option>';
    marketSel.innerHTML = '<option value="">Pilih Daerah terlebih dahulu</option>';
    marketSel.disabled = true;
    
    let activeRegionId = null;
    
    try {
        // Load regions
        const regData = await api('/regions');
        (regData.regions||[]).forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            if (currentMerchantProfile.market && currentMerchantProfile.market.region_id == r.id) {
                opt.selected = true;
                activeRegionId = r.id;
            }
            regSel.appendChild(opt);
        });
        
        // Load markets for active region
        if (activeRegionId) {
            await loadMarketsForEditProfile(activeRegionId, currentMerchantProfile.market_id);
        }
    } catch(e) {}
}

async function loadMarketsForEditProfile(regionId, selectedMarketId = null) {
    const marketSel = document.getElementById('ep-market');
    marketSel.innerHTML = '<option value="">Pilih Pasar</option>';
    marketSel.disabled = true;
    try {
        const mData = await api(`/regions/${regionId}/markets`);
        (mData.markets||[]).forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.name;
            if (selectedMarketId && m.id == selectedMarketId) {
                opt.selected = true;
            }
            marketSel.appendChild(opt);
        });
        marketSel.disabled = false;
    } catch(e) {}
}

document.getElementById('ep-region')?.addEventListener('change', async function() {
    const regionId = this.value;
    const warning = document.getElementById('ep-region-warning');
    
    if (!regionId) {
        document.getElementById('ep-market').innerHTML = '<option value="">Pilih Daerah terlebih dahulu</option>';
        document.getElementById('ep-market').disabled = true;
        if (warning) warning.style.display = 'none';
        return;
    }
    
    await loadMarketsForEditProfile(regionId);
    
    // Show warning if changed region from current
    const origRegionId = currentMerchantProfile?.market?.region_id;
    if (warning) {
        warning.style.display = (regionId != origRegionId) ? 'flex' : 'none';
    }
});

document.getElementById('edit-profile-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('ep-btn');
    btn.textContent = 'Memproses...';
    btn.disabled = true;
    
    const store_name = document.getElementById('ep-store-name').value.trim();
    const store_address = document.getElementById('ep-store-address').value.trim();
    const market_id = parseInt(document.getElementById('ep-market').value);
    const description = document.getElementById('ep-description').value.trim();
    
    try {
        const res = await api('/merchant/profile', 'PUT', {
            store_name,
            store_address,
            market_id,
            description
        });
        showToast(res.message || 'Profil toko berhasil diperbarui!');
        closeProfileModal();
        
        // Reload page after a delay to reflect verification pending if region/store changed
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } catch(err) {
        const msg = err.data?.message || 'Gagal memperbarui profil toko';
        showToast(msg, 'error');
        btn.textContent = 'Simpan Perubahan';
        btn.disabled = false;
    }
});
</script>
@endpush
