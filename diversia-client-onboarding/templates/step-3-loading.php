<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-loading">
    <div class="dco-loading__spinner">
        <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
            <circle cx="25" cy="25" r="20" fill="none" stroke="#F47920" stroke-width="4"
                    stroke-dasharray="80 40" stroke-linecap="round">
                <animateTransform attributeName="transform" type="rotate"
                                  from="0 25 25" to="360 25 25" dur="1s" repeatCount="indefinite"/>
            </circle>
        </svg>
    </div>
    <h2 class="dco-loading__title" data-i18n data-en="Evaluating your application..." data-es="Evaluando su solicitud...">Evaluating your application...</h2>
    <p class="dco-loading__body" data-i18n data-en="Our AI system is reviewing your profile. This may take a few seconds." data-es="Nuestro sistema de inteligencia artificial está revisando su perfil. Esto puede tardar unos segundos.">Our AI system is reviewing your profile. This may take a few seconds.</p>
    <div class="dco-loading__dots">
        <span></span><span></span><span></span>
    </div>
</div>
