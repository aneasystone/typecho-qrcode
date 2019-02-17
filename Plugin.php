<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 为每篇文章显示一个二维码，可以直接用手机扫描，方便手机查看
 *
 * @package QRCode
 * @author aneasystone
 * @version 1.1
 * @link http://www.aneasystone.com
 */
class QRCode_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    	Typecho_Plugin::factory('Widget_Archive')->footer = array('QRCode_Plugin', 'footer');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('QRCode_Plugin', 'render');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    	/** 二维码尺寸 */
    	$name = new Typecho_Widget_Helper_Form_Element_Text(
    			'size', NULL, '200', _t('二维码尺寸'), _t('不宜设置的太小，对于比较长的网址生成的二维码可能不正确'));
    	$form->addInput($name);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render($text, $widget)
    {
        // 获取自定义字段 hideQrCode 的值
        $cid = Typecho_Widget::widget('Widget_Archive')->cid;
        $db = Typecho_Db::get();
        $hideQrCode = $db->fetchAll($db->select()->from('table.fields')->where('cid = ?', $cid)->where('name = ?', 'hideQrCode'));
        if($hideQrCode[0] && $hideQrCode[0]['str_value'] == 'true') {
            return $text;
        }

        $content = $text;
        $content .= '<style>';
        $content .= '  #qrcodeWrapper { margin:0 auto; text-align: center; }';
        $content .= '  #qrcodeWrapper img { margin:0 auto; }';
        $content .= '</style>';
        $content .= '<div id="qrcodeWrapper"">';
        $content .= 	'<div id="qrcode"></div>';
        $content .= 	'<div>扫描二维码，在手机上阅读！</div>';
        $content .= '</div>';
        return $content;
    }

	public static function footer()
	{
    $currentPath = Helper::options()->pluginUrl . '/QRCode/';
    	echo '<script type="text/javascript" src="' . $currentPath . 'assets/qrcode.min.js"></script>' . "\n";
        $js =
<<<EOL
<script type="text/javascript">
$(document).ready(function() {

    var qrcode = document.getElementById("qrcode");
    if (qrcode == null) {
        return;
    }

    var url = window.location.href;
    var hashIndex = url.indexOf('#');
    var qrUrl = hashIndex < 0 ? url : url.substring(0, hashIndex);

    new QRCode(document.getElementById("qrcode"), {
    	text: qrUrl,
    	width: {SIZE},
    	height: {SIZE},
    	colorDark : "#000000",
    	colorLight : "#ffffff",
    	correctLevel : QRCode.CorrectLevel.H
    });
});
</script>
EOL;
        $size = Typecho_Widget::widget('Widget_Options')->plugin('QRCode')->size;
        $size = $size <= 0 ? 200 : $size;
        echo str_replace("{SIZE}", $size, $js);
	}
}
