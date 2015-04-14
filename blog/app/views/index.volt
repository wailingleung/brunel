<!DOCTYPE html>
<html>
<head>
    <title>{% if meta['title'] is defined %}{{ meta['title'] }}{% else %}Dutch Frontiers Blog{% endif %}</title>
    <meta charset="UTF-8">
    <meta name="description" content="{% if meta['description'] is defined %}{{ substr(meta['description'],0,255) }}{% else %}Blog{% endif %}">
    <meta name="keywords" content="Dutch Frontiers,Blog">
    <meta name="author" content="Dutch Frontiers">
    <link rel="stylesheet" type="text/css"  href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css"/>
</head>
<body>
{{ content() }}
</body>
</html>