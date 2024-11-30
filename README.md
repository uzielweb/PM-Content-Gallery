# PM-Content-Gallery
This is a simple gallery to insert into the Joomla content
## SYNTAX
{pmgallery}folder{/pmgallery}
or
{pmgallery}folder|description{/pmgallery}
### Template of the gallery
At this moment the default and only template of the gallery is based on OWL Carousel.


Estrutura Básica da Tag no Artigo

A tag do artigo segue este formato:

{gallery}nome_da_pasta|parametro1=valor1|parametro2=valor2|...{/gallery}

Onde:

    nome_da_pasta é o nome da pasta onde as imagens da galeria estão localizadas.
    Os parâmetros são passados dentro do conteúdo da tag, separados por |.

Exemplo de Como Inserir os Parâmetros

Aqui está um exemplo prático de como usar os parâmetros dentro da tag:

{gallery}galeria|description=Natal de Cristo|gallery_type=grid|images_per_row=3|thumbnail_width=600|thumbnail_height=600|show_name=show_image_name|height=16by9|modal=1|loop=1|autoplay=1|nav=1|dots=1|lazyload=1|dotseach=1{/gallery}

Explicação dos Parâmetros no Exemplo

    galeria: A pasta onde estão as imagens (exemplo: images/galeria).
    description=Natal de Cristo: Define a descrição da galeria como "Natal de Cristo".
    gallery_type=grid: Exibe as imagens em formato de grid (grade).
    images_per_row=3: Exibe 3 imagens por linha.
    thumbnail_width=600 e thumbnail_height=600: Define as miniaturas das imagens com tamanho de 600x600 pixels.
    show_name=show_image_name: Exibe o nome da imagem na galeria.
    height=16by9: Define a proporção de altura da galeria como widescreen (16:9).
    modal=1: A galeria será carregada em uma janela modal (pop-up).
    loop=1: A galeria será repetida automaticamente ao final (loop ativado).
    autoplay=1: As imagens da galeria serão reproduzidas automaticamente.
    nav=1: Ativa a navegação por setas na galeria.
    dots=1: Exibe os pontos de navegação na parte inferior da galeria.
    lazyload=1: Ativa o carregamento preguiçoso, ou seja, as imagens são carregadas somente quando visíveis na tela.
    dotseach=1: Exibe 1 ponto de navegação por vez.


  As mesmas opções estão disponíveis na configuração geral do plugin.
