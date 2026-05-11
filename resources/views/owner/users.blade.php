@extends('layouts.owner')
@section('title', 'Manajemen User')

@push('styles')
<style>
    .main-wrapper { margin-left:260px; padding:25px; color:white; min-height:100vh; }
    .card { background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px; }
    label { display:block; color:#8b949e; font-size:11px; margin-bottom:5px; font-weight:bold; }
    input, select, textarea { width:100%; background:#0d1117; border:1px solid #30363d; color:white; padding:12px; border-radius:8px; margin-bottom:15px; box-sizing:border-box; }
    .btn { background:#ff9f43; color:#0d1117; padding:12px; border-radius:8px; border:none; font-weight:bold; cursor:pointer; width:100%; }
    table { width:100%; border-collapse:collapse; }
    th { text-align:left; color:#8b949e; font-size:11px; padding:10px; border-bottom:2px solid #30363d; }
    td { padding:12px 10px; border-bottom:1px solid #30363d; font-size:13px; }
    .badge { padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold; text-transform:uppercase; }
    .modal { display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.8); }
    .modal-content { background:#161b22; margin:10% auto; padding:25px; border:1px solid #30363d; width:400px; border-radius:12px; }
    @media (max-width:768px) {
        .main-wrapper { margin-left:0 !important; padding:15px; }
        .grid-layout { grid-template-columns:1fr !important; }
        th, td { padding:8px 4px; font-size:11px; }
        .modal-content { width:90%; margin:20% auto; }
    }
</style>
@endpush

@section('content')
<div class="main-wrapper">
    <div class="grid-layout" style="display:grid; grid-template-columns:1fr 2fr; gap:25px;">

        {{-- FORM TAMBAH/EDIT --}}
        <div class="card">
            <h3 style="font-family:'Syne'; color:#ff9f43; margin-top:0;">
                {{ isset($editData) && $editData['id'] ? 'Edit' : 'Tambah' }} User
            </h3>
            <form method="POST" action="{{ route('users.simpan') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="id_edit" value="{{ $editData['id'] ?? '' }}">

                <label>Username (Login)</label>
                <input type="text" name="username" value="{{ $editData['username'] ?? '' }}" autocomplete="off" required>

                <label>Password</label>
                <input type="text" name="password" value="{{ $editData['password'] ?? '' }}" autocomplete="new-password" required>

                <label>Nama Cabang</label>
                <input type="text" id="nm_cb" name="nama_cabang" value="{{ $editData['nama_cabang'] ?? '' }}"
                    oninput="autoAlamat()" autocomplete="off" list="data_cabang" required>

                <label>Alamat Cabang</label>
                <textarea id="al_cb" name="alamat_cabang" rows="2" autocomplete="off" required>{{ $editData['alamat_outlet'] ?? '' }}</textarea>

                <label>Level Akun</label>
                <select name="level" id="lvl_akun" onchange="autoOwner()">
                    <option value="user" {{ ($editData['level'] ?? '') == 'user' ? 'selected' : '' }}>Kasir (User)</option>
                    <option value="owner" {{ ($editData['level'] ?? '') == 'owner' ? 'selected' : '' }}>Owner</option>
                </select>

                <button type="submit" class="btn">💾 Simpan User & Cabang</button>
            </form>
        </div>

        {{-- TABEL DATA USER --}}
        <div class="card">
            <h3 style="font-family:'Syne'; margin-top:0;">Data Akun Aktif</h3>
            <table>
                <thead>
                    <tr>
                        <th>CABANG & ALAMAT</th>
                        <th>USER/PASS</th>
                        <th>LEVEL</th>
                        <th style="text-align:center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $row)
                    @php $clr = ($row->level == 'owner') ? '#ff9f43' : '#00d4aa'; @endphp
                    <tr>
                        <td>
                            <b>{{ $row->outlet->nama_cabang ?? '-' }}</b><br>
                            <small style="color:#8b949e;">{{ $row->outlet->alamat_outlet ?? '-' }}</small>
                        </td>
                        <td>
                            {{ $row->username }}<br>
                            <small style="color:#8b949e; font-family:monospace;">{{ $row->password }}</small>
                        </td>
                        <td>
                            <span class="badge" style="background:{{ $clr }}20; color:{{ $clr }};">
                                {{ $row->level }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; justify-content:center; align-items:center;">
                                <a href="{{ route('users.edit', $row->id) }}" style="text-decoration:none; background:#1f2937; padding:6px 10px; border-radius:6px; font-size:13px;">✏️</a>
                                @if($row->id != 1)
                                <a href="{{ route('users.hapus', $row->id) }}"
                                    onclick="return confirm('Hapus user ini?')"
                                    style="text-decoration:none; background:#1f2937; padding:6px 10px; border-radius:6px; font-size:13px;">🗑️</a>
                                @else
                                <span style="display:inline-block; width:37px;"></span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<datalist id="data_cabang">
    @foreach($outlets as $o)
    <option value="{{ $o->nama_cabang }}" data-alamat="{{ $o->alamat_outlet }}">
    @endforeach
</datalist>
@endsection

@push('scripts')
<script>
function autoOwner() {
    const lvl = document.getElementById('lvl_akun').value;
    if(lvl === 'owner') {
        document.getElementById('nm_cb').value = "Laras Laundry Owner";
        document.getElementById('al_cb').value = "Kendali Laras Laundry";
    }
}
function autoAlamat() {
    const val = document.getElementById('nm_cb').value;
    const list = document.getElementById('data_cabang').options;
    for (let i = 0; i < list.length; i++) {
        if (list[i].value === val) {
            document.getElementById('al_cb').value = list[i].getAttribute('data-alamat');
            break;
        }
    }
}
</script>
@endpush
