<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

//设置外观
function themeConfig($form) {
    $logoUrl = new Typecho_Widget_Helper_Form_Element_Text('logoUrl', NULL, NULL, _t('站点LOGO地址'), _t('在这里填入一个图片URL地址, 以在网站标题前加上一个LOGO'));
    $form->addInput($logoUrl);
    
    $sidebarBlock = new Typecho_Widget_Helper_Form_Element_Checkbox('sidebarBlock', 
    array('ShowRecentPosts' => _t('显示最新文章'),
    'ShowRecentComments' => _t('显示最近回复'),
    'ShowCategory' => _t('显示分类'),
    'ShowArchive' => _t('显示归档'),
    'ShowOther' => _t('显示其它杂项')),
    array('ShowRecentPosts', 'ShowRecentComments', 'ShowCategory', 'ShowArchive', 'ShowOther'), _t('侧边栏显示'));
    
    $form->addInput($sidebarBlock->multiMode());
}

//评论回调函数
    function threadedComments($comments, $options) {
        
        $commentClass = '';
        if ($comments->authorId) {
            if ($comments->authorId == $comments->ownerId) {
                $commentClass .= ' comment-by-author';
            } else {
                $commentClass .= ' comment-by-user';
            }
        }
        
        $commentLevelClass = $comments->levels > 0 ? ' comment-child' : ' comment-parent';

        //初始化一些变量
        $singleCommentOptions = Typecho_Config::factory();
        $singleCommentOptions->setDefault(array(
            'before'        =>  '<ol class="comment-list">',
            'after'         =>  '</ol>',
            'beforeAuthor'  =>  '',
            'afterAuthor'   =>  '',
            'beforeDate'    =>  '',
            'afterDate'     =>  '',
            'dateFormat'    =>  Helper::options()->commentDateFormat,
            'replyWord'     =>  _t('回复TA'),
            'commentStatus' =>  _t('你给我提交的什么东西，还要我亲自检查……'),
            'avatarSize'    =>  36,
            'defaultAvatar' =>  Helper::options()->themeUrl . 'img/gravatar.jpg'
        ));
?>
<li itemscope itemtype="http://schema.org/UserComments" id="<?php $comments->theId(); ?>" class="comment-body<?php
    if ($comments->levels > 0) {
        echo ' comment-child';
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even');
    } else {
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass;
?>">
    <div class="comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
        <span itemprop="image"><?php get_cdn_avatar($singleCommentOptions->avatarSize, $singleCommentOptions->defaultAvatar); ?></span>
        <cite class="fn" itemprop="name"><?php $singleCommentOptions->beforeAuthor();
        $comments->author();
        $singleCommentOptions->afterAuthor(); ?></cite>
    </div>
    <div class="comment-meta">
        <a href="<?php $comments->permalink(); ?>"><time itemprop="commentTime" datetime="<?php $comments->date('c'); ?>"><?php $singleCommentOptions->beforeDate();
        $comments->date($singleCommentOptions->dateFormat);
        $singleCommentOptions->afterDate(); ?></time></a>
        <?php if ('waiting' == $comments->status) { ?>
        <em class="comment-awaiting-moderation"><?php $singleCommentOptions->commentStatus(); ?></em>
        <?php } ?>
    </div>
    <div class="comment-content" itemprop="commentText">
    <?php $comments->content(); ?>
    </div>
    <div class="comment-reply">
        <?php $comments->reply($singleCommentOptions->replyWord); ?>
    </div>
    <?php if ($comments->children) { ?>
    <div class="comment-children" itemprop="discusses">
        <?php $comments->threadedComments(); ?>
    </div>
    <?php } ?>
</li>
<?php
    }
	
//头像CDN
function get_cdn_avatar($size = 32, $default = NULL)
{
$comments = Typecho_Widget::widget('Widget_Comments_Archive');
$rating = Helper::options()->commentsAvatarRating;
$mailHash = md5(strtolower($comments->mail));
if (Typecho_Request::isSecure()) {
$host = 'https://secure.gravatar.com'; //HTTPS头像源
} else {
$host = 'http://gravatar.duoshuo.com'; //HTTP头像源
}
$url = $host . '/avatar/';
$url .= $mailHash;
$url .= '?s=' . $size;
$url .= '&amp;r=' . $rating;
$url .= '&amp;d=' . $default;
echo '<img class="avatar" src="' . $url . '" alt="' .
$comments->author . '" width="' . $size . '" height="' . $size . '" />';
}

//获得读者墙   
function getFriendWall()   
{   
    $db = Typecho_Db::get();   
    $sql = $db->select('COUNT(author) AS cnt', 'author', 'url', 'mail')   
              ->from('table.comments')   
              ->where('status = ?', 'approved')   
              ->where('type = ?', 'comment')   
              ->where('authorId = ?', '0')   
              ->where('mail != ?', Typecho_Widget::widget('Widget_Users_Author@' . 1, array('uid' => 1))->mail)   //排除自己上墙   
              ->group('author')   
              ->order('cnt', Typecho_Db::SORT_DESC)   
              ->limit('15');    //读取几位用户的信息   
    $result = $db->fetchAll($sql);   
	$mostactive = "";
    
    if (count($result) > 0) {   
        $maxNum = $result[0]['cnt'];   
        foreach ($result as $value) {   
            $mostactive .= '<li><a target="_blank" rel="nofollow" href="' . $value['url'] . '"><span class="pic" style="background: url(https://secure.gravatar.com/avatar/'.md5(strtolower($value['mail'])).'?s=36&d=&r=G) no-repeat; "></span><em>' . $value['author'] . '</em><strong>+' . $value['cnt'] . '</strong><br />' . $value['url'] . '</a></li>';       
        }   
        echo $mostactive;   
    }   
}   

    /**
     * 解析并输出友情链接
     * 
	 * @author qining
     * @access public
     * @param string $slug 页面标题
     * @param string $tag 标题的html tag
     * @param string $listTag 列表的html tag
     * @return void
     */
    function PageToLinks_output($slug = 'links', $tag = 'h2', $listTag = 'ul')
    {
        /** 获取数据库支持 */
        $db = Typecho_Db::get();
        
        /** 获取文本 */
        $contents = $db->fetchObject($db->select('text')->from('table.contents')
        ->where('slug = ?', $slug)->limit(1));
        
        if (!$contents) {
            return;
        }
        
        $text = $contents->text;
        $cats = preg_split("/<\/(ol|ul)>/i", $text);
        
        foreach ($cats as $cat) {
            $item = trim($cat);
            
            if ($item) {
                $matches = array_map('trim', preg_split("/<(ol|ul)[^>]*>/i", $item));
                if (2 == count($matches)) {
                    list ($title, $list) = $matches;
                    echo "<$tag>" . strip_tags($title) . "</$tag>";
                    echo "<$listTag>" . $list . "</$listTag>";
                }
            }
        }
    }