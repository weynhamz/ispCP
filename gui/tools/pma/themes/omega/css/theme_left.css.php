<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * navigation css file from theme
 *
 * @version $Id$
 * @package phpMyAdmin-theme
 * @subpackage ispCP_Omega
 */

    // unplanned execution path
    if (!defined('PMA_MINIMUM_COMMON')) {
        exit();
    }

    $forIE = false;
    if (PMA_USR_BROWSER_AGENT == 'IE' && PMA_USR_BROWSER_VER < 7)
        $forIE = true;

    // 2007-08-24 (mkkeck)
    //            Get the whole http_url for the images
    $ipath = $_SESSION['PMA_Theme']->getImgPath();

    // 2007-08-24 (mkkeck)
    //            Get font-sizes
    if (isset($GLOBALS['PMA_Config']) && $GLOBALS['PMA_Config']->get('fontsize') !== null) {
        $pma_fsize = $GLOBALS['PMA_Config']->get('fontsize');
    } else if (isset($_SESSION['PMA_Config']) && $_SESSION['PMA_Config']->get('fontsize')) {
        $pma_fsize = $_SESSION['PMA_Config']->get('fontsize');
    } else {
        if (isset($_COOKIE['pma_fontsize'])) {
            $pma_fsize = $_COOKIE['pma_fontsize'];
        }
    }
    $pma_fsize = preg_replace("/[^0-9]/", "", $pma_fsize);
    if (!empty($pma_fsize)) {
        $pma_fsize = ($pma_fsize * 0.01);
    } else {
        $pma_fsize = 1;
    }
    if (!isset($usr_fsize)) {
        $usr_fsize = 11;
    }
    if ( isset($GLOBALS['cfg']['FontSizePrefix']) && !empty($GLOBALS['cfg']['FontSizePrefix']) ) {
        $funit = strtolower($GLOBALS['cfg']['FontSizePrefix']);
    }
    if (!isset($funit) || ($funit!='px' && $funit != 'pt')) {
        $funit = 'px';
    }
    $fsize = ($usr_fsize - 2);
    if ($pma_fsize) {
        $fsize = number_format( (intval($usr_fsize) * $pma_fsize), 0 );
    }

?>
/******************************************************************************/
/* general tags */
html, td, body {
<?php if (!empty($GLOBALS['cfg']['FontFamily'])) { ?>
    font-family:         <?php echo $GLOBALS['cfg']['FontFamily']; ?>;
<?php } ?>
    font-size:           <?php echo $fsize . $funit; ?>;
}
body {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    background-image:    url('<?php echo $ipath; ?>wbg_left.jpg');
    background-color:	 #878585;
    background-repeat:   repeat-y;
    background-position: 0px 0px;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
<?php if (! empty($GLOBALS['cfg']['FontFamily'])) { ?>
    font-family:         <?php echo $GLOBALS['cfg']['FontFamily']; ?>;
<?php } ?>
    margin-left:         0px;
    margin-right:        0px;
    padding-left:        15px;
    padding-right:       10px;
}
p, h1, h2, h3, form {
    margin:              0px;
    padding:             0px;
}

a img                          { border:     none;   }
form                           { display:    inline; }
select                         { width:      100%;   }
select optgroup, select option { font-style: normal; }
button                         { display:    inline; }

/******************************************************************************/
/* classes */

/* leave some space between icons and text */
.icon {
    margin-left:         1px;
    margin-right:        1px;
    vertical-align:      middle;
}


/******************************************************************************/
/* specific elements */

div#pmalogo,
div#leftframelinks,
div#databaseList {
    border-bottom:       1px solid <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    margin-bottom:       1px;
    padding-bottom:      1px;
}
div#pmalogo, div#leftframelinks { text-align: center; }
div#databaseList                { text-align: left;   }

div#leftframelinks .icon {
    margin:              0;
    padding:             0;
}

div#leftframelinks a img.icon {
    border:              1px none <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    margin:              0;
    padding:             2px;
}

div#leftframelinks a:hover {
    background:          <?php echo $GLOBALS['cfg']['NaviPointerBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviPointerColor']; ?>;
}

/* serverlist */
#body_leftFrame #list_server {
    list-style-image:    url(<?php echo $ipath; ?>s_host.png);
    list-style-position: inside;
    list-style-type:     none;
    margin:              0;
    padding:             0;
}

#body_leftFrame #list_server li {
    font-size:           95%;
    margin:              0;
    padding:             0;
}

/* leftdatabaselist */
div#left_tableList ul {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    font-size:           95%;
    list-style-type:     none;
    list-style-position: outside;
    margin:              0;
    padding:             0;
}

div#left_tableList ul ul {
    font-size:           100%;
}

div#left_tableList a {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    text-decoration:     none;
}

div#left_tableList a:hover {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    text-decoration:     underline;
}

div#left_tableList li {
    margin:              0;
    padding:             0;
    white-space:         nowrap;
}

<?php if ($GLOBALS['cfg']['BrowseMarkerColor']) { ?>
/* marked items */
div#left_tableList > ul li.marked > a,
div#left_tableList > ul li.marked {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
}
div#left_tableList ul li.marked, div#left_tableList ul li.marked a,
div#left_tableList ul li.marked ul li.marked, div#left_tableList ul li.marked ul li.marked a {
    background:          <?php echo $GLOBALS['cfg']['BrowseMarkerBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['BrowseMarkerColor']; ?>;
}
div#left_tableList ul li.marked ul, div#left_tableList ul li.marked ul li, div#left_tableList ul li.marked ul a {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
}
<?php } ?>

<?php if ( $GLOBALS['cfg']['LeftPointerEnable'] ) { ?>
div#left_tableList > ul li:hover > a,
div#left_tableList > ul li:hover{
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
}
div#left_tableList ul li:hover, div#left_tableList ul li:hover a, div#left_tableList ul li a:hover,
div#left_tableList ul li:hover ul li:hover, div#left_tableList ul li:hover ul li:hover a, div#left_tableList ul li ul li a:hover {
    background:          <?php echo $GLOBALS['cfg']['NaviPointerBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviPointerColor']; ?>;
}
<?php if ($GLOBALS['cfg']['BrowseMarkerColor']) { ?>
div#left_tableList ul li.marked a:hover, div#left_tableList ul li.marked ul li.marked a:hover {
    background:          <?php echo $GLOBALS['cfg']['BrowseMarkerBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['BrowseMarkerColor']; ?>;
}
<?php } ?>
div#left_tableList ul li:hover ul, div#left_tableList ul li:hover ul li,div#left_tableList ul li:hover ul a {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
}
<?php } ?>

div#left_tableList img {
    padding:             0;
    vertical-align:      middle;
}

div#left_tableList ul ul {
    background:          <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    border-bottom:       1px none <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    border-left:         1px none <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    margin-left:         0;
    padding-left:        15px;
    padding-bottom:      1px;
}

ul#databaseList, ul#databaseList ul {
    margin:                      0px 0px 0px 0px;
    padding:                     0px 0px 0px 0px;
}
ul#databaseList li {
    border-bottom:               none;
    font-weight:                 bold;
    list-style:                  none;
    margin:                      2px 0px 2px 0px;
    padding:                     0px 0px 2px 0px;
    white-space:                 nowrap;
}
ul#databaseList li ul li {
    border-bottom:               none;
    margin:                      0px 0px 0px 10px;
    padding:                     0px 0px 0px 0px;
}
ul#databaseList {
    background-color:            <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    color:                       #8ed8fd;
}
ul#databaseList a:link, ul#databaseList a:active, ul#databaseList a:visited {
    background-color:            <?php echo $GLOBALS['cfg']['NaviBackground']; ?>;
    background-image:            url('<?php echo $ipath; ?>b_sdb.png');
    background-position:         <?php echo $left; ?>;
    background-repeat:           no-repeat;
    color:                       <?php echo $GLOBALS['cfg']['NaviColor']; ?>;
    font-weight:                 normal;
    padding-<?php echo $left; ?>: 12px;
    text-decoration:             none;
}
<?php if ( $GLOBALS['cfg']['LeftPointerEnable'] ) { ?>
ul#databaseList a:hover,
ul#databaseList > li:hover > a, ul#databaseList > ul li:hover > a {
    background-color:    <?php echo $GLOBALS['cfg']['NaviPointerBackground']; ?>;
    color:               <?php echo $GLOBALS['cfg']['NaviPointerColor']; ?>;
}
<?php } ?>
ul#databaseList a:hover { text-decoration: underline; }


