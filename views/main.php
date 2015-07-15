<!doctype html>
    <!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]><html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>EventFeed</title>
        <meta name="viewport" content="width=device-width,initial-scale=1, maximum-scale=1">
        <base href="/">
        <link rel="stylesheet" href="/static/css/normalize.css">
        <link rel="stylesheet" href="/static/css/main.css">
        <link rel="shortcut icon" type="image/png" href="/favicon.png">
        <link rel="icon" href="/favicon.ico">
        <script src="/static/js/vendor/modernizr-2.7.1.min.js"></script>
    </head>
    <body>
        <header>
            <p>EventFeed</p>
        </header>
        <div id="columns">
<?php

    foreach ($posts as $post) {
        ob_start();

?>

            <div data-timestamp="{{ post.timestamp }}" class="post {{ post.type }}">
            {% if post.type=='text' %}
                <p><strong>{{ post.author }} :</strong> {{ post.text }}</p>
            {% elif post.type=='hybrid' %}
                <img src="/uploads/{{ post.image }}">
                <div class="text"><p><strong>{{ post.author }} :</strong> {{ post.text }}</p></div>
            {% elif post.type=='image' %}
                <img src="/uploads/{{ post.image }}">
            {% endif %}
            </div>
<?php

        $content = ob_get_clean();

    }

?>
            <div class="column">
            {% for post in column %}
                <div data-timestamp="{{ post.timestamp }}" class="post {{ post.type }}">
                {% if post.type=='text' %}
                    <p><strong>{{ post.author }} :</strong> {{ post.text }}</p>
                {% elif post.type=='hybrid' %}
                    <img src="/uploads/{{ post.image }}">
                    <div class="text"><p><strong>{{ post.author }} :</strong> {{ post.text }}</p></div>
                {% elif post.type=='image' %}
                    <img src="/uploads/{{ post.image }}">
                {% endif %}
                </div>
            {% endfor %}
            </div>
        {% endfor %}
        </div>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/static/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
        <script src="/static/js/plugins.js"></script>
        <script src="/static/js/main.js"></script>
    </body>
</html>