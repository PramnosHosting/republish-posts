<?php

function rpPostMeta()
{
    add_meta_box('rpPostExtrabox', 'Republish Post',
            'rpPostExtrabox', 'post', 'normal', 'default');
}


function rpPostExtrabox($post)
{
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style(
        'republish-posts-admin-ui-css',
        plugins_url() . '/republish-posts/media/jquery-ui.min.css',
        false,
        PLUGIN_VERSION,
        false
    );
    $rpRepublishDate = get_post_meta($post->ID, 'rpRepublishDate', true);
    if ((int)$rpRepublishDate > (time()-(3600*24))) {
        $dateToDisplay = date('d/m/Y', $rpRepublishDate);
    } else {
        $dateToDisplay = '';
    }
    ?>
    <p>
        <label for="rpRepublishDate"><strong>Republish Date</strong></label><br>
        <input name="rpRepublishDate" id="rpRepublishDate" value="<?php echo $dateToDisplay; ?>" />
    </p>

    <p>
        <label><strong>Choose the time</strong></label><br>
        <input type="number" class="small-text" id="rpRepublisHour" name="rpRepublisHour" min="0" max="24" step="1" value="<?php echo date('H', $rpRepublishDate);?>">:
        <input type="number" class="small-text" id="rpRepublisMinute" name="rpRepublisMinute" min="0" max="60" step="1" value="<?php echo date('i', $rpRepublishDate);?>">:
        <input type="number" class="small-text" id="rpRepublisSecond" name="rpRepublisSecond" min="0" max="60" step="1" value="<?php echo date('s', $rpRepublishDate);?>">
    </p>


    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#rpRepublishDate').datepicker({
                dateFormat : 'dd/mm/yy'
            });
        });
    </script>
    <?php
    rpUpdatePostsHourly();
}


function rpSavePostMeta($postID)
{
    global $post;

    if ($post->post_type == "post"
        && isset($_POST['rpRepublishDate'])
        && isset($_POST['rpRepublisHour'])
        && isset($_POST['rpRepublisMinute'])
        && isset($_POST['rpRepublisSecond'])) {

        $date = explode('/', $_POST['rpRepublishDate']);
        if (count($date) != 3) {
            delete_post_meta($postID, 'rpRepublishDate');
            return;
        }
        $day = $date[0];
        $month = $date[1];
        $year = $date[2];

        $finalDate = mktime(
            (int)$_POST['rpRepublisHour'], (int)$_POST['rpRepublisMinute'],
            (int)$_POST['rpRepublisSecond'], $month, $day, $year
        );
        if ($finalDate > (time()-(3600*24))) {
            update_post_meta($postID, 'rpRepublishDate', $finalDate);
        } else {
            delete_post_meta($postID, 'rpRepublishDate');
        }


    }

}
