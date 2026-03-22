<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" 
    rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" 
    crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" 
integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>

<?php 
if(!defined('ABSPATH'))
    {
        exit;
    }

class ContactForm_design
{
    public function __construct()
    {
        add_shortcode("cf_frontend", array($this, "cf_frontend_design"));
        add_shortcode("cf_query_list", array($this, "cf_query_display"));
        add_action("init", array($this, "cf_submission"));
        add_action("init", array($this, "cf_post"));
    }

    public function cf_frontend_design()
    {
        ob_start(); ?>
        <div class="form_container d-flex justify-content-center bg-success py-3 w-50">
             <form class="contact_form p-3" method="post" enctype="multipart/form-data">
                <label for="full_name">Full Name</label><br/>
                <input id="full_name" type="text" name="full_name" required><br/>
                <label for="email_address">Email</label><br/>
                <input id="email_address" type="email" name="email_address" required><br/>
                <div class="email_error"></div>
                <label for="subject">Subject</label><br/>
                <input id="subject" type="text" name="subject" maxlength="100" required><br/>
                <p> <span id="subject_char_counter">0</span>/100 characters </p>
                <label for="message">Message</label><br/>
                <textarea id="message" name="message" maxlength="300" required></textarea><br/>
                <p> <span id="message_char_counter">0</span>/300 characters </p>
                <label for="file">Upload File</label><br/>
                <input id="file" type="file" name="file" required><br/>
                <label for="inquiry">Inquiry Type</label><br/>
                <select id="inquiry" name="inquiry" required>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select> <br/>
                <?php wp_nonce_field("cf_plugin_action", "cf_plugin_nonce") ?>
                <button class="submit_btn border border-0 rounded-1" type="submit" name="cf_submit">Send Inquiry</button>
            </form>
        </div>
       
       <?php
    return ob_get_clean();
    }
    public function cf_submission()
    {
        if(is_admin())
            {
                return;
            }

        if(isset($_POST["cf_submit"]))
            {
                if(! isset($_POST["cf_plugin_nonce"]) || ! wp_verify_nonce($_POST["cf_plugin_nonce"], "cf_plugin_action"))
                {
                    return;
                }

                if(!isset($_POST['full_name'], $_POST['email_address'], $_POST['subject'], $_POST['message'],
                $_POST['inquiry']))
                {
                    return;
                }

                if(!isset($_POST["email_address"]) || !is_email($_POST["email_address"]))
                    {
                        return;
                    }

                //Sanitize Input Fields
                $name = sanitize_text_field($_POST["full_name"]);
                $email = sanitize_email($_POST["email_address"]);
                $subject = sanitize_text_field($_POST["subject"]);
                $message = sanitize_textarea_field($_POST["message"]);
                $inquiry = sanitize_text_field($_POST["inquiry"]);
                $inquiry_type = ['Low', 'Medium', 'High'];

                if(!in_array($inquiry, $inquiry_type, true))
                    {
                        return;
                    }
                
                //File Upload Functionality
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                $attached_url = '';
                if(!empty($_FILES['file']['name']))
                    {
                        $uploaded_file = $_FILES['file'];
                        $upload_overrides = array('test_form' => false,
                                                    'mimes' => array(
                                                        'jpg|jpeg|jpe' => 'image/jpeg',
                                                        'png' => 'image/png',
                                                        'pdf' => 'application/pdf',
                                                        'docx' => 'application/docx'
                                                    ));
                        $move_file = wp_handle_upload($uploaded_file, $upload_overrides);
                        if($move_file && !isset($move_file['error']))
                            {
                                $attached_url = $move_file['url'];
                            }
                    }
               

                    //Create a Post
                    $create_cf_post = array("post_type" => "cf_query_post",
                    "post_title" => $subject,
                    "post_content" => $message,
                    "post_status" => "publish",
                    "meta_input" => array("Full_Name" => $name, 
                                            "Email" => $email,
                                            "Inquiry_Status" => $inquiry,
                                            "Attachment" => $attached_url));

                    $insert_cf_post =   wp_insert_post($create_cf_post);
                     if($insert_cf_post && !is_wp_error($insert_cf_post))
                        {
                            wp_redirect(home_url('/customer-query-list/'));
                            exit;
                        }
            }
    }

    public function cf_post(){
        $labels = array('name' => 'Queries',
        'singular_name' => 'Query',
        'add_new' => 'Add New Query',
        'add_new_item' => 'Add New Query',
        'new_item' => 'New Query',
        'view_item' => 'View Query',
        'search_items' => 'Search Query',
        'not_found' => 'No Queries Found',
        'menu_name' => 'Contact Form');

        $args = array('labels'  => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'contact-query'),
        'show_in_rest'       => true, // Enable Gutenberg editor
        'supports'           => array('title', 'editor'),
        'menu_icon'          => 'dashicons-list-view',);

        register_post_type("cf_query_post", $args);
    }

    public function cf_query_display()
    {
        $query_list = new WP_Query(array('post_type' => 'cf_query_post',
        'post_status' => "publish"));

        ob_start();

        if($query_list -> have_posts())
            {
                while($query_list -> have_posts())
                    {
                        $query_list -> the_post();
                        $query_full_name = get_post_meta(get_the_ID(), 'Full_Name', true);
                        $query_email = get_post_meta(get_the_ID(), 'Email', true);
                        $query_status = get_post_meta(get_the_ID(), 'Inquiry_Status', true);
                        $query_attachment = get_post_meta(get_the_ID(), 'Attachment', true);
                        

                        echo '<div class="bg-primary p-3 rounded-2">';
                            echo '<h3 class="text-light fs-4"> <span class="text-warning fw-bold fs-3"> Subject: </span>' . esc_html(get_the_title()) . '</h3>';
                            echo '<p class="text-light fs-4"> <span class="text-warning fw-bold fs-3"> Message: </span>' . wp_kses_post(get_the_content()) . '</p>';
                            echo '<h2 class="text-light mt-3"> User Information </h2>';
                            echo '<br/>';
                            echo '<p class="text-light fs-3"> <span class="text-warning fw-bold fs-5"> Name: </span>'. esc_html($query_full_name) . '</p>';
                            echo '<p class="text-light fs-3"> <span class="text-warning fw-bold fs-5"> Email: </span>' . esc_html($query_email) . '</p>';
                            echo '<p class="text-light fs-3"> <span class="text-warning fw-bold fs-5"> Status: </span>' . esc_html($query_status) . '</p>';
                            echo '<p> <a class="text-light" href=" '. esc_url($query_attachment) .'"> View Attachment </a> </p>';
                        echo '</div>';
                        
                    }
                    wp_reset_postdata();
            }
            else{
                echo '<p> No Query Found! </p>'; 
            }
        return ob_get_clean();
    }
    
}