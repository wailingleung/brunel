{% for item in blogItem %}
    <h1>{{ item.blogTitle }}</h1>
    {{ date('d-m-Y',strtotime(item.blogInsertDate)) }}<br>
    {{ item.blogDescription }}<br>
    Geschreven door: {{ item.blogAuthor }}<br>
    <br>
    {% if commentSaved is defined %}
        Commentaar is opgeslagen.
    {% elseif messages is defined %}
        {% for message in messages %}
            {{ message }}<br>
        {% endfor %}
    {% endif %}

    <form action="/blogitem/{{ item.blogId }}" method="post">
        <input type="hidden" name="blogId" value="{{ item.blogId }}">
        Naam: <input type="text" name="blogCommentAuthor" value="{% if postValue['blogCommentAuthor'] is defined %}{{ postValue['blogCommentAuthor'] }}{% endif %}"><br>
        Reactie: <textarea name="blogComment">{% if postValue['blogCommentAuthor'] is defined %}{{ postValue['blogComment'] }}{% endif %}</textarea><br>
        <input type="submit" value="Verzenden">
    </form>
{% endfor %}
<br>
{# {{ dump(blogComments) }} #}
<h2>{{ blogComments|length }} reacties</h2>
{% if blogComments|length >0 %}
    {% for itemComment in blogComments %}
        {{ date('d-m-Y',strtotime(itemComment.blogCommentInsertDate)) }}<br>
        {{ itemComment.blogComment }}<br>
        Geschreven door: {{ itemComment.blogCommentAuthor }}<br><br>
    {% endfor %}
{% endif %}



