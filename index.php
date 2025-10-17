<?php
require_once __DIR__ . '/config.php';

$apiKey = 'edb9248c31ad4f2e9f3c892ffc90bc52';

function buildNewsApiUrlWithKey(string $apiKey, ?string $query): string
{
    if ($query === null || trim($query) === '') {
        
        return 'https://newsapi.org/v2/top-headlines?country=us&category=technology&pageSize=12&apiKey=' . urlencode($apiKey);
    }

    $encoded = urlencode($query);
    return 'https://newsapi.org/v2/everything?language=id&q=' . $encoded . '&pageSize=12&sortBy=publishedAt&apiKey=' . urlencode($apiKey);
}

function formatIndonesianDate(?string $isoString): string
{
    if ($isoString === null || $isoString === '') return '';
    try {
        $dt = new DateTime($isoString, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
    } catch (Exception $e) {
        return $isoString;
    }
}

function h(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$endpoint = buildNewsApiUrlWithKey($apiKey, $query);

$apiError = null;
$articles = [];

try {
    $raw = http_request_get($endpoint);
    $decoded = json_decode($raw, true);

    if (!isset($decoded['status']) || $decoded['status'] !== 'ok') {
        $apiError = 'Gagal memuat berita.';
    } else {
        $articles = $decoded['articles'] ?? [];
    }
} catch (Throwable $e) {
    $apiError = 'Gagal memuat berita.';
}

$imagePlaceholder = 'https://via.placeholder.com/400x250?text=No+Image';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Berita Indonesia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        (function() {
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark))
                document.documentElement.classList.add('dark');
        })();
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e3a8a, #0f172a);
            background-size: 300% 300%;
            animation: bgShift 10s ease infinite;
        }
        html { scroll-behavior: smooth; }
        @keyframes bgShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }
        .glass:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.6s ease forwards; }
    </style>
</head>
<body class="min-h-screen text-gray-200 dark:text-gray-100 dark:bg-gray-900">

<header id="siteHeader" class="sticky top-0 z-50 shadow-md transition-colors duration-300" id="top">
    <div id="headerBg" class="bg-gradient-to-r from-[#1E3A8A] to-[#3B82F6] transition duration-300">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="text-3xl">📰</div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white drop-shadow-sm tracking-wide">BREAKING NEWS</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button id="themeToggle" class="hidden md:inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-md transition">
                        <span id="themeLabel">🌙</span>
                        <span class="hidden sm:inline">Mode</span>
                    </button>
                    <button id="menuToggle" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-md text-white hover:bg-white/10 transition" aria-label="Menu">
                        <!-- Heroicons Bars-3 -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>
            <form method="GET" action="" class="mt-3 max-w-xl">
                <div class="relative">
                    <input type="text" name="q" value="<?=h($query)?>" placeholder="Cari berita terkini..." class="w-full rounded-md border border-white/20 bg-white/10 placeholder-white/70 text-white px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-white/40 transition" />
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white text-sm px-3 py-1.5 rounded transition">Cari</button>
                </div>
            </form>
            <nav class="hidden md:block mt-4">
                <ul class="flex items-center gap-6 text-white/90">
                    <li>
                        <a href="#top" class="group inline-flex items-center gap-2 hover:text-white transition ease-in-out duration-300">
                            <!-- Heroicons Home -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                            </svg>
                            <span class="relative">
                                Home
                                <span class="block h-0.5 bg-white/70 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#berita" class="group inline-flex items-center gap-2 hover:text-white transition ease-in-out duration-300">
                            <!-- Heroicons Newspaper -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h6m-6 3h6m-6 3h6m-6 3h6M6 7.5h.008v.008H6V7.5zm0 3h.008v.008H6V10.5zm0 3h.008v.008H6V13.5zm0 3h.008v.008H6V16.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5V6.75A2.25 2.25 0 0 1 5.25 4.5h11.25A2.25 2.25 0 0 1 18.75 6.75V19.5A1.5 1.5 0 0 1 17.25 21H4.5A1.5 1.5 0 0 1 3 19.5z" />
                            </svg>
                            <span class="relative">
                                Berita
                                <span class="block h-0.5 bg-white/70 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#tentang" class="group inline-flex items-center gap-2 hover:text-white transition ease-in-out duration-300">
                            <!-- Heroicons Information-circle -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25v5.25m0-8.25h.008v.008H11.25z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                            </svg>
                            <span class="relative">
                                Tentang
                                <span class="block h-0.5 bg-white/70 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="#kontak" class="group inline-flex items-center gap-2 hover:text-white transition ease-in-out duration-300">
                            <!-- Heroicons Phone -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15H19.5a2.25 2.25 0 0 0 2.25-2.25v-1.372a1.125 1.125 0 0 0-.852-1.09l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a.75.75 0 0 1-1.21-.063 12.035 12.035 0 0 1-3.114-3.114.75.75 0 0 1-.063-1.21l1.293-.97a1.125 1.125 0 0 0 .417-1.173L8.962 4.102A1.125 1.125 0 0 0 7.872 3.25H6.5A2.25 2.25 0 0 0 4.25 5.5v1.25z" />
                            </svg>
                            <span class="relative">
                                Kontak
                                <span class="block h-0.5 bg-white/70 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- Mobile menu -->
        <div id="mobileMenu" class="md:hidden overflow-hidden max-h-0 opacity-0 transition-all duration-300">
            <nav class="px-4 pb-4">
                <ul class="flex flex-col gap-3 text-white/90">
                    <li><a href="#top" class="inline-flex items-center gap-2 py-2"><span>🏠</span> Home</a></li>
                    <li><a href="#berita" class="inline-flex items-center gap-2 py-2"><span>🗞️</span> Berita</a></li>
                    <li><a href="#tentang" class="inline-flex items-center gap-2 py-2"><span>ℹ️</span> Tentang</a></li>
                    <li><a href="#kontak" class="inline-flex items-center gap-2 py-2"><span>📞</span> Kontak</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

 <main id="berita" class="max-w-7xl mx-auto px-4 py-10">
    <?php if ($apiError): ?>
        <div class="text-center text-red-400 bg-red-900/30 p-4 rounded"><?=h($apiError)?></div>
    <?php elseif (empty($articles)): ?>
        <div class="text-center bg-white/10 p-6 rounded glass text-white">Belum ada berita untuk ditampilkan.</div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $article): ?>
                <?php
                    $title = h($article['title'] ?? '');
                    $desc = h($article['description'] ?? '');
                    $source = h($article['source']['name'] ?? '');
                    $url = h($article['url'] ?? '#');
                    $img = $article['urlToImage'] ?: $imagePlaceholder;
                    $date = formatIndonesianDate($article['publishedAt'] ?? '');
                ?>
                <article class="glass fade-in rounded-xl overflow-hidden shadow-lg">
                    <img src="<?=$img?>" alt="<?=$title?>" class="w-full h-48 object-cover">
                    <div class="p-4 flex flex-col">
                        <h2 class="text-lg font-semibold mb-2"><?=$title?></h2>
                        <p class="text-sm text-gray-200 mb-4 line-clamp-3"><?=$desc?></p>
                        <div class="text-xs text-gray-300 mb-2"><?=$source?> · <?=$date?></div>
                        <a href="<?=$url?>" target="_blank" class="mt-auto inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm text-center transition">Baca Selengkapnya</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<section id="tentang" class="max-w-7xl mx-auto px-4 py-24">
    <div class="glass rounded-2xl p-8 text-white fade-in">
        <h2 class="text-2xl font-bold mb-3">Tentang</h2>
        <p class="text-white/90 leading-relaxed">Portal ini menampilkan berita terkini dari berbagai sumber terpercaya. Nikmati tampilan modern, mode gelap, dan interaksi yang halus saat Anda menjelajah berita.</p>
    </div>
    
    <div id="kontak" class="glass rounded-2xl p-8 text-white fade-in mt-10">
        <h2 class="text-2xl font-bold mb-3">Kontak</h2>
        <p class="text-white/90">Untuk saran dan masukan, silakan hubungi kami melalui email: <span class="underline">x.zibit35.8@gmail.com</span>.</p>
    </div>
 </section>

<footer class="text-center text-gray-300 text-sm py-8">
    Dibuat oleh <b>Steven Teody Budiono (3B)</b> – Data oleh <a href="https://newsapi.org" class="underline">NewsAPI.org</a>
</footer>

 <script>
 // Dark mode toggle
 (function(){
     var btn = document.getElementById('themeToggle');
     var label = document.getElementById('themeLabel');
     if (btn) {
         btn.addEventListener('click', function(){
             var root = document.documentElement;
             var isDark = root.classList.toggle('dark');
             localStorage.setItem('theme', isDark ? 'dark' : 'light');
             if (label) label.textContent = isDark ? '☀️' : '🌙';
         });
     }
 })();

 // Sticky header darken on scroll & mobile menu toggle
 (function(){
     var header = document.getElementById('siteHeader');
    var headerBg = document.getElementById('headerBg');
     var menuBtn = document.getElementById('menuToggle');
     var mobile = document.getElementById('mobileMenu');
     var open = false;

     window.addEventListener('scroll', function(){
         var scrolled = window.scrollY > 8;
         if (scrolled) {
             header.classList.add('shadow-lg');
            if (headerBg) headerBg.style.filter = 'brightness(0.9)';
         } else {
             header.classList.remove('shadow-lg');
            if (headerBg) headerBg.style.filter = '';
         }
     });

     if (menuBtn && mobile) {
         menuBtn.addEventListener('click', function(){
             open = !open;
             if (open) {
                 mobile.style.maxHeight = mobile.scrollHeight + 'px';
                 mobile.style.opacity = '1';
             } else {
                 mobile.style.maxHeight = '0px';
                 mobile.style.opacity = '0';
             }
         });
     }
 })();
 </script>

</body>
</html>
