@php
    $normalizedStageName = strtoupper($stageName);
@endphp

@if (str_contains($normalizedStageName, 'CLIENT'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M14 52V23l18-11 18 11v29"></path>
        <path d="M24 52V36h16v16"></path>
        <circle cx="32" cy="25" r="5"></circle>
    </svg>
@elseif (str_contains($normalizedStageName, 'SALES'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M14 40h12l8 8h16"></path>
        <path d="M14 31h10l7 7 6-6"></path>
        <path d="M38 24h12v12"></path>
        <path d="m37 37 13-13"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'KOM'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <circle cx="23" cy="24" r="6"></circle>
        <circle cx="43" cy="24" r="6"></circle>
        <path d="M13 50v-6a10 10 0 0 1 20 0v6"></path>
        <path d="M33 50v-6a10 10 0 0 1 18-6"></path>
        <path d="M20 12h24l6 6v10H14V18z"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'CTP'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <rect x="18" y="10" width="28" height="44" rx="3"></rect>
        <path d="M25 22h14M25 31h14M25 40h8"></path>
        <path d="m25 50 4-4 4 4 8-9"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'ENG'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M14 48h36"></path>
        <rect x="18" y="16" width="28" height="30" rx="2"></rect>
        <path d="m23 40 17-17 5 5-17 17h-5z"></path>
        <path d="M24 26h10M24 32h5"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'SCC'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M29 10h8l1.5 7a18 18 0 0 1 5 2l6-4 5 7-5 5a18 18 0 0 1 1 6l7 3-3 8-7-1a18 18 0 0 1-4 5l2 7-8 3-4-6a18 18 0 0 1-6 0l-4 6-8-3 2-7a18 18 0 0 1-4-5l-7 1-3-8 7-3a18 18 0 0 1 1-6l-5-5 5-7 6 4a18 18 0 0 1 5-2z"></path>
        <circle cx="33" cy="33" r="9"></circle>
    </svg>
@elseif ($normalizedStageName === 'PM' || str_contains($normalizedStageName, 'PROJECT MANAGER'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <rect x="16" y="12" width="32" height="42" rx="3"></rect>
        <path d="M24 24h16M24 33h16M24 42h9"></path>
        <path d="m20 24 2 2 4-5"></path>
        <path d="m20 33 2 2 4-5"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'DOCON') || str_contains($normalizedStageName, 'DOC'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M20 10h20l8 8v36H20z"></path>
        <path d="M40 10v8h8"></path>
        <path d="M25 28h17M25 37h17M25 46h10"></path>
        <path d="m18 48-5 5 5 5"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'PFM'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <rect x="14" y="16" width="36" height="32" rx="3"></rect>
        <path d="M22 26h20M22 34h12"></path>
        <path d="M20 48v6M44 48v6"></path>
        <path d="m38 38 5 5 9-12"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'PURCHASING') || str_contains($normalizedStageName, 'PROCUREMENT'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M14 18h7l5 24h25l5-18H25"></path>
        <circle cx="31" cy="50" r="4"></circle>
        <circle cx="48" cy="50" r="4"></circle>
        <path d="M31 30h16M34 36h10"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'FABRICATION') || str_contains($normalizedStageName, 'FABRIKASI'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M12 52h40"></path>
        <path d="M17 52V28l12 8V28l12 8V18h10v34"></path>
        <path d="M21 44h6M33 44h6"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'BINA'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M16 50V25l16-9 16 9v25"></path>
        <path d="M24 50V34h16v16"></path>
        <path d="M22 27h20"></path>
        <path d="M12 50h40"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'ASSEMB') || str_contains($normalizedStageName, 'ASSY'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <circle cx="23" cy="20" r="6"></circle>
        <circle cx="43" cy="20" r="6"></circle>
        <path d="M13 48v-8a10 10 0 0 1 20 0v8"></path>
        <path d="M33 48v-8a10 10 0 0 1 18-6"></path>
        <path d="M25 34h14"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'QC') || str_contains($normalizedStageName, 'TEST') || str_contains($normalizedStageName, 'FAT'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <rect x="18" y="12" width="28" height="40" rx="3"></rect>
        <path d="M25 12h14l-2-4H27z"></path>
        <path d="m24 32 6 6 14-16"></path>
        <circle cx="45" cy="45" r="9"></circle>
        <path d="M45 40v5l4 3"></path>
    </svg>
@elseif (str_contains($normalizedStageName, 'PACK') || str_contains($normalizedStageName, 'SHIPMENT'))
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <path d="M14 23 32 13l18 10-18 10z"></path>
        <path d="M14 23v20l18 10 18-10V23"></path>
        <path d="M32 33v20"></path>
        <path d="m23 18 18 10"></path>
    </svg>
@else
    <svg viewBox="0 0 64 64" aria-hidden="true">
        <rect x="18" y="12" width="28" height="40" rx="3"></rect>
        <path d="M25 12h14l-2-4H27z"></path>
        <path d="M24 25h16M24 34h16M24 43h10"></path>
    </svg>
@endif
