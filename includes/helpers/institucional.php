<?php

function institutionalSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function institutionalCards(): array
{
    return [
        [
            'title' => 'Missão',
            'icon' => 'far fa-compass',
            'text' => 'Oferecer soluções de transporte de cargas e passageiros que maximizem a satisfação dos clientes nos diversos segmentos.',
        ],
        [
            'title' => 'Visão',
            'icon' => 'far fa-eye',
            'text' => 'Ser referência no mercado pelo compromisso e transparência com seus clientes e colaboradores.',
        ],
        [
            'title' => 'Valores',
            'icon' => 'far fa-handshake',
            'text' => 'Credibilidade, honestidade ética, valorização e desenvolvimento dos nossos colaboradores, trabalho em equipe, responsabilidade social e ambiental.',
        ],
    ];
}

function institutionalHistory(): array
{
    return [
        'A história da MCM Empresas tem início na década de 1970, a partir da visão empreendedora do Sr. Antônio Machado, que acreditou no potencial de desenvolvimento da região norte de Goiás, território que hoje corresponde ao Estado do Tocantins. Movido pelo espírito de inovação e pelo compromisso com o progresso regional, deu os primeiros passos no agronegócio, setor que marcou o início de uma trajetória sólida e consistente.',
        'Com o passar dos anos, a MCM ampliou seus horizontes e consolidou sua atuação em segmentos estratégicos, como a distribuição de combustíveis e, a partir de 2004, a concessão da marca Scania, referência mundial em soluções para transporte sustentável.',
        'Sob a liderança do Sr. Eduardo Machado, a MCM viveu um importante ciclo de expansão e profissionalização, tornando-se representante oficial da Scania nos estados do Tocantins, Maranhão e Piauí. Hoje, oferecemos um portfólio completo de produtos e serviços voltados ao transporte de cargas e passageiros, apoiando nossos clientes em cada etapa de suas operações.',
        'Nossa estrutura é um dos grandes diferenciais da empresa. Contamos com cinco concessionárias localizadas estrategicamente nas cidades de Gurupi, Araguaína, Imperatriz, São Luís e Teresina, sendo esta última com uma nova e moderna unidade inaugurada em 2024. Complementam essa presença um Posto de Serviços em São Luís, uma Loja de Peças e uma Oficina Móvel em Balsas (MA) e outra na região de Palmas (TO). A sede administrativa e financeira está localizada em Palmas, oferecendo suporte integrado a todas as unidades.',
        'Somos mais de 250 colaboradores, além de dezenas de parceiros terceirizados, unidos por um mesmo propósito: entregar um atendimento consultivo, técnico e encantador, sempre orientado às necessidades dos nossos clientes. A MCM acredita no desenvolvimento contínuo das pessoas, no respeito às relações e na construção de parcerias duradouras. Esses pilares sustentam nossa trajetória e impulsionam nosso futuro.',
    ];
}
