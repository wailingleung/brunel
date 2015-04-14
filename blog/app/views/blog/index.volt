{% for item in blogItems %}
    {{ date('d-m-Y',strtotime(item.blogInsertDate)) }} - <a href="/blogitem/{{ item.blogId }}">{{ item.blogTitle }}</a>  <br>
    Geschreven door: {{ item.blogAuthor }}
    <br> <br>
{% endfor %}
