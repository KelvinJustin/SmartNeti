{include file="customer/header.tpl"}

<!-- Dynamic Greeting -->
<div id="greeting-container" class="greeting-container">
  <div id="greeting-text" class="greeting-text"></div>
</div>

<!-- Carousel -->
<div>
  <div class="carousel">
    <ul class="slides">
      <input type="radio" name="radio-buttons" id="img-1" checked />
      <li class="slide-container">
        <div class="slide-image">
          <img src="{$app_url}/ui/ui/images/promo-banner-1.png" loading="lazy" alt="SmartNeti WiFi Package Promo">
        </div>
        <div class="carousel-controls">
          <label for="img-3" class="prev-slide">
            <span>&lsaquo;</span>
          </label>
          <label for="img-2" class="next-slide">
            <span>&rsaquo;</span>
          </label>
        </div>
      </li>
      <input type="radio" name="radio-buttons" id="img-2" />
      <li class="slide-container">
        <div class="slide-image">
          <img src="{$app_url}/ui/ui/images/promo-banner-2.png" loading="lazy" alt="SmartNeti Student Internet Banner">
        </div>
        <div class="carousel-controls">
          <label for="img-1" class="prev-slide">
            <span>&lsaquo;</span>
          </label>
          <label for="img-3" class="next-slide">
            <span>&rsaquo;</span>
          </label>
        </div>
      </li>
      <input type="radio" name="radio-buttons" id="img-3" />
      <li class="slide-container">
        <div class="slide-image">
          <img src="{$app_url}/ui/ui/images/promo-banner-3.jpg" loading="lazy" alt="SmartNeti Special Offer">
        </div>
        <div class="carousel-controls">
          <label for="img-2" class="prev-slide">
            <span>&lsaquo;</span>
          </label>
          <label for="img-1" class="next-slide">
            <span>&rsaquo;</span>
          </label>
        </div>
      </li>
      <div class="carousel-dots">
        <label for="img-1" class="carousel-dot" id="img-dot-1"></label>
        <label for="img-2" class="carousel-dot" id="img-dot-2"></label>
        <label for="img-3" class="carousel-dot" id="img-dot-3"></label>
      </div>
    </ul>
  </div>
</div>

{function showWidget pos=0}
    {foreach $widgets as $w}
        {if $w['position'] == $pos}
            {$w['content']}
        {/if}
    {/foreach}
{/function}


{assign rows explode(".", $_c['dashboard_Customer'])}
{assign pos 1}
{foreach $rows as $cols}
    {if $cols == 12}
        <div class="row">
            <div class="col-md-12">
                {showWidget widgets=$widgets pos=$pos}
            </div>
        </div>
        {assign pos value=$pos+1}
    {else}
        {assign colss explode(",", $cols)}
        <div class="row">
            {foreach $colss as $c}
                <div class="col-md-{$c}">
                    {showWidget widgets=$widgets pos=$pos}
                </div>
                {assign pos value=$pos+1}
            {/foreach}
        </div>
    {/if}
{/foreach}

<script>
document.addEventListener('DOMContentLoaded', function() {
    const greetings = {
        morning: [
            { en: 'Good morning!', ny: 'Mwadzuka bwanji!' },
            { en: 'Rise and shine!', ny: 'Mwadzuka bwanji' },
            { en: 'Wishing you a wonderful morning.', ny: 'Tikufunirani m\'mawa wabwino.' },
            { en: 'Start your day connected.', ny: 'Yambani tsiku lanu muli olumikizidwa.' },
            { en: 'Have a productive morning.', ny: 'Khalani ndi m\'mawa wopindulitsa.' },
            { en: 'Welcome to a new day!', ny: 'Takulandirani ku tsiku latsopano!' }
        ],
        afternoon: [
            { en: 'Good afternoon!', ny: 'Masana abwino!' },
            { en: 'Hope your day is going well.', ny: 'Tikukhulupirira tsiku lanu likuyenda bwino.' },
            { en: 'Stay connected this afternoon.', ny: 'Pitirizani kulumikizidwa masana ano.' },
            { en: 'Wishing you a pleasant afternoon.', ny: 'Tikufunirani masana osangalatsa.' },
            { en: 'Enjoy the rest of your day.', ny: 'Sangalalani ndi tsiku lanu lotsalalo.' },
            { en: 'Thanks for staying with us.', ny: 'Zikomo pokhala nafe.' }
        ],
        evening: [
            { en: 'Good evening!', ny: 'Madzulo abwino!' },
            { en: 'Relax and stay connected.', ny: 'Pumulani ndipo pitirizani kulumikizidwa.' },
            { en: 'Hope you\'ve had a great day.', ny: 'Tikukhulupirira mwakhala ndi tsiku labwino.' },
            { en: 'Enjoy your evening online.', ny: 'Sangalalani ndi madzulo anu pa intaneti.' },
            { en: 'Wishing you a peaceful evening.', ny: 'Tikufunirani madzulo amtendere.' },
            { en: 'We\'re happy to have you here.', ny: 'Ndife okondwa kukhala nanu.' }
        ],
        night: [
            { en: 'Good night!', ny: 'Usiku wabwino!' },
            { en: 'Sleep well.', ny: 'Gonani bwino.' },
            { en: 'Have a restful night.', ny: 'Khalani ndi usiku wopumula.' },
            { en: 'Stay connected whenever you need us.', ny: 'Khalani olumikizidwa nthawi iliyonse mukatifuna.' },
            { en: 'Thanks for choosing us tonight.', ny: 'Zikomo potisankha usiku uno.' },
            { en: 'See you tomorrow!', ny: 'Tionana mawa!' }
        ]
    };

    const hour = new Date().getHours();
    let timeOfDay;

    if (hour >= 5 && hour < 12) {
        timeOfDay = 'morning';
    } else if (hour >= 12 && hour < 17) {
        timeOfDay = 'afternoon';
    } else if (hour >= 17 && hour < 21) {
        timeOfDay = 'evening';
    } else {
        timeOfDay = 'night';
    }

    const timeGreetings = greetings[timeOfDay];
    const randomGreeting = timeGreetings[Math.floor(Math.random() * timeGreetings.length)];
    const useChichewa = Math.random() > 0.5;
    const greetingText = useChichewa ? randomGreeting.ny : randomGreeting.en;

    const greetingElement = document.getElementById('greeting-text');
    const greetingContainer = document.getElementById('greeting-container');

    greetingElement.textContent = greetingText;

    setTimeout(function() {
        greetingContainer.classList.add('vanishing');
        setTimeout(function() {
            greetingContainer.style.display = 'none';
        }, 1500);
    }, 5000);
});

// Mobile swipe gesture support for carousel
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.carousel');
    const slides = document.querySelectorAll('input[name="radio-buttons"]');
    let currentSlide = 0;
    let touchStartX = 0;
    let touchEndX = 0;

    carousel.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);

    carousel.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);

    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > swipeThreshold) {
            // Find current checked radio
            slides.forEach((slide, index) => {
                if (slide.checked) {
                    currentSlide = index;
                }
            });

            if (diff > 0) {
                // Swipe left - go to next slide
                currentSlide = (currentSlide + 1) % slides.length;
            } else {
                // Swipe right - go to previous slide
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            }

            slides[currentSlide].checked = true;
        }
    }
});
</script>

{if isset($hostname) && $hchap == 'true' && $_c['hs_auth_method'] == 'hchap'}
    <script type="text/javascript" src="/ui/ui/scripts/md5.js"></script>
    <script type="text/javascript">
        var hostname = "http://{$hostname}/login";
        var user = "{$_user['username']}";
        var pass = "{$_user['password']}";
        var dst = "{$apkurl}";
        var authdly = "2";
        var key = hexMD5('{$key1}' + pass + '{$key2}');
        var auth = hostname + '?username=' + user + '&dst=' + dst + '&password=' + key;
        document.write('<meta http-equiv="refresh" target="_blank" content="' + authdly + '; url=' + auth + '">');
    </script>
{/if}
{include file="customer/footer.tpl"}