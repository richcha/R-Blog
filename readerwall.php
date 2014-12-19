<?php
/**
* 榜上有名
*
* @package custom
*/

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
 $this->need('header.php');
?>

<div class="col-mb-12 col-8" id="main" role="main">
    <article class="post" itemscope itemtype="http://schema.org/BlogPosting">
        <h1 class="post-title" itemprop="name headline"><a itemtype="url" href="<?php $this->permalink() ?>"><?php $this->title() ?></a></h1>
        <ul class="readers-list" itemprop="articleBody">
            <?php echo getFriendWall();?>
        </ul>
    </article>
    <?php $this->need('comments.php'); ?>
</div><!-- end #main-->

<?php $this->need('sidebar.php'); ?>
<?php $this->need('footer.php'); ?>