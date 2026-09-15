<section class="institutional-page">
    <div class="institutional-wrap">
        <h1 class="institutional-title">Conheça nossa empresa</h1>

        <div class="institutional-cards">
            <?php foreach ($institutional_cards as $card): ?>
                <article class="institutional-card">
                    <h2><?php echo htmlspecialchars($card['title']); ?></h2>
                    <p><?php echo htmlspecialchars($card['text']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="institutional-section">
            <h2>Nossa História</h2>
            <div class="institutional-section-body">
                <?php foreach ($institutional_history as $paragraph): ?>
                    <p><?php echo htmlspecialchars($paragraph); ?></p>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="institutional-section">
            <h2>Manifesto MCM</h2>
            <div class="manifesto-box">
                <img src="<?php echo htmlspecialchars($base_url); ?>/index_files/manifesto_mcm.jpg" alt="Manifesto MCM">
            </div>
        </section>
    </div>
</section>
