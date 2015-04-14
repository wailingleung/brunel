<?php

class BlogController extends ControllerBase
{
    public function indexAction() //show Blog Homepage
    {
        try {
            //retrieve blog items
            $blogItems= Blogposts::find(["order" => "blogInsertDate desc"]);
            $this->view->setVar("blogItems", $blogItems);
        }catch (Phalcon\Exception $e) {
            echo $e->getMessage();
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }

    public function showAction($id) //show Blog detail page
    {
        try {
           //$this->view->disable();
           $messages=$this->saveAction();

           if ($messages){
               $postValue=$this->request->getPost();
               $this->view->setVar("messages", $messages);
           }

           //uri contains string 'success'
           if (strpos($this->request->getURI(),'success') !== false ){
               $this->view->setVar("commentSaved",1);
           }

           //retrieve blog
           $blogItem= Blogposts::find(
                ["blogId = '".$id."'"]
           );
           //retrieve list of comments
           $blogComments=Blogcomments::find([
               "conditions" => "blogId = ?1",
               "bind"       => [1 => $id],
               "order" => "blogCommentInsertDate desc"
           ]);
           // meta tag title , description
           foreach ( $blogItem as $blogItemValue) {
               $meta['title']=$blogItemValue->blogTitle;
               $meta['description']=$blogItemValue->blogDescription;
           }

           $this->view->setVar("meta", $meta);

            $this->view->setVar("postValue", $postValue);
           $this->view->setVar("blogItem", $blogItem);
           $this->view->setVar("blogComments", $blogComments);

        }catch (Phalcon\Exception $e) {
            echo $e->getMessage();
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }

    private function saveAction() //validate blog comment / insert comment
    {
        try {
            // Check if request has made with POST
            if ($this->request->isPost() == true) {

                // Access POST data
                $id = $this->request->getPost("blogId");

                $blogComment=new Blogcomments();

                //Store and check for errors
                $success = $blogComment->create($this->request->getPost(),
                        array('blogId', 'blogCommentAuthor','blogComment'));

                if ($success) {
                    //Make a redirect
                    return $this->response->redirect("blogitem/".$id."/success");
                } else {
                    $messages=$blogComment->validation();
                    return $messages;
                }
            }
            return false;
        }catch (Phalcon\Exception $e) {
            echo $e->getMessage();
        }catch (PDOException $e){
            echo $e->getMessage();
        }
    }
}

