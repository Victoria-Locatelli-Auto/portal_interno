<div class="institutional-page">
    <header class="institutional-page-header">
        <div class="institutional-page-heading">
            <span class="institutional-page-icon" aria-hidden="true">
                <i class="far fa-building"></i>
            </span>

            <div>
                <span class="institutional-page-kicker">Sobre a MCM</span>
                <h1>Institucional</h1>
            </div>
        </div>
    </header>

    <section class="institutional-principles" aria-label="Missão, visão e valores">
        <?php foreach ($institutional_cards as $card): ?>
            <article class="principle-card">
                <span class="principle-icon" aria-hidden="true">
                    <i class="<?php echo htmlspecialchars($card['icon']); ?>"></i>
                </span>
                <h2><?php echo htmlspecialchars($card['title']); ?></h2>
                <p><?php echo htmlspecialchars($card['text']); ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="institutional-section history-section" aria-labelledby="institutional-history-title">
        <div class="section-heading">
            <span class="section-icon" aria-hidden="true"><i class="fas fa-timeline"></i></span>
            <div>
                <span class="section-kicker">Nossa trajetória</span>
                <h2 id="institutional-history-title">Nossa história</h2>
            </div>
        </div>

        <div class="institutional-history">
            <?php foreach ($institutional_history as $paragraph): ?>
                <p><?php echo htmlspecialchars($paragraph); ?></p>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="institutional-section manifesto-section" aria-labelledby="institutional-manifesto-title">
        <div class="section-heading">
            <span class="section-icon" aria-hidden="true"><i class="far fa-star"></i></span>
            <div>
                <span class="section-kicker">Nosso compromisso</span>
                <h2 id="institutional-manifesto-title">Manifesto MCM</h2>
            </div>
        </div>

        <div class="manifesto-box">
            <img
                src="<?php echo htmlspecialchars($base_url); ?>/index_files/manifesto_mcm.jpg"
                alt="Manifesto MCM"
                loading="lazy"
            >
        </div>
    </section>
</div>
