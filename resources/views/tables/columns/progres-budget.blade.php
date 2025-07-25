@php
$budget = $getState()['budget'];
$percentage = (int) $getState()['percentage'];

// Batasi width maksimum 100% untuk tampilan bar
$displayBarWidth = min($percentage, 100);

// Warna bar berdasarkan kondisi
$progressColor = match (true) {
$percentage > 100 => '#e74c3c', // Overbudget = merah
$percentage > 50 => '#27ae60', // Hijau
default => '#2980b9', // Biru
};

// Warna teks berdasarkan kontras
$textColor = $percentage > 60 ? 'text-white' : 'text-gray-700';
@endphp

<div class="progress-container">
    <div class="progress-bar" style="width: {{ $displayBarWidth }}%; background-color: {{ $progressColor }};">
        <div class="progress-text">
            <small class="text-white">
                {{ $percentage }}%
            </small>
        </div>
    </div>
</div>

<style>
    .progress-container {
        width: 100%;
        max-width: 160px;
        background-color: #e5e7eb;
        border-radius: 0.375rem;
        height: 1.5rem;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
        height: 100%;
        transition: width 0.3s ease-in-out, background-color 0.3s ease-in-out;
        border-radius: 0.375rem;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .progress-bar::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 0.375rem;
        background: linear-gradient(135deg,
                rgba(255, 255, 255, 0.2) 25%,
                rgba(255, 255, 255, 0) 25%,
                rgba(255, 255, 255, 0) 50%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0.2) 75%,
                rgba(255, 255, 255, 0) 75%,
                rgba(255, 255, 255, 0) 100%);
        background-size: 40px 40px;
        animation: progress-bar-stripes 1s linear infinite;
        z-index: 0;
    }

    .progress-text {
        position: relative;
        z-index: 1;
        width: 100%;
        text-align: center;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    @keyframes progress-bar-stripes {
        from {
            background-position: 40px 0;
        }

        to {
            background-position: 0 0;
        }
    }
</style>