<div class="business-card">
    <div class="date-info">
        <i class="fas fa-calendar-alt"></i> {{ now()->format('d F Y') }}
    </div>
    <div class="user-info">
        <h5><i class="fas fa-user-circle"></i>{{ $user->nama_lengkap ?? '-' }}</h5>
        @if(isset($toko))
            <p><i class="fas fa-store"></i>{{ $toko->nama_toko }}</p>
        @endif
    </div>
</div>

@once
    @push('styles')
    <style>
        .business-card {
            background: linear-gradient(135deg, #007bff 0%, #00bfff 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .business-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        .business-card .user-info h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.4rem;
        }
        .business-card .user-info p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }
        .business-card .date-info {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 0.85rem;
            opacity: 0.8;
        }
        .user-info i {
            margin-right: 10px;
        }
    </style>
    @endpush
@endonce
