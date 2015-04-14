<?php
use Phalcon\Validation\Validator\PresenceOf;

class Blogcomments extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     */
    public $blogCommentId;

    /**
     *
     * @var integer
     */
    public $blogId;

    /**
     *
     * @var string
     */
    public $blogComment;

    /**
     *
     * @var string
     */
    public $blogCommentAuthor;

    /**
     *
     * @var string
     */
    public $blogCommentInsertDate;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('blogId', 'Blogposts', 'blogId', NULL);
        $this->skipAttributes(array('blogCommentInsertDate'));

    }

    public function validation(){

        $validation = new Phalcon\Validation();

        $validation->add('blogCommentAuthor', new PresenceOf(array(
            'message' => 'Naam  is een verplicht veld'
        )));

        $validation->add('blogComment', new PresenceOf(array(
            'message' => 'Reactie is een verplicht veld'
        )));

       return  $validation->validate($_POST);
    }
}
