<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{TR_CLIENT_WEBTOOLS_PAGE_TITLE}</title>
		<meta http-equiv="Content-Type" content="text/html; charset={THEME_CHARSET}" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta http-equiv="Content-Script-Type" content="text/javascript" />
		<meta name="robots" content="nofollow, noindex" />
		<link href="{THEME_COLOR_PATH}/css/ispcp.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
			<!--
			function CopyText(inputname) {
				window.opener.document.forms[0].other_dir.value = document.forms[0].elements[inputname].value;
				window.opener.document.forms[0].use_other_dir.checked = true;
				self.close();
			}
			//-->
		</script>
	</head>

	<body>
		<form action="">
			<table width="450" cellpadding="5" cellspacing="5" style="empty-cells:show">
				<!-- BDP: page_message -->
				<tr>
					<td width="100%" style="white-space:nowrap;" class="content3"><span class="message">{MESSAGE}</span></td>
				</tr>
				<!-- EDP: page_message -->
				<tr>
					<td width="410" class="content3" style="white-space:nowrap;" colspan="3"><strong>{TR_DIRECTORY_TREE}</strong></td>
				</tr>
				<tr>
					<td width="40" class="content2"></td>
					<td width="310" class="content2" style="white-space:nowrap;"><strong>{TR_DIRS}</strong></td>
					<td width="60" class="content2" style="white-space:nowrap;" align="center"><strong>{TR__ACTION}</strong></td>
				</tr>
				<!-- BDP: dir_item -->
				<tr>
					<!-- BDP: list_item -->
					<td width="40" class="content" align="center"><img src="{THEME_COLOR_PATH}/images/icons/bc_{ICON}.png" alt="" /></td>
					<td width="310" class="content" style="white-space:nowrap;"><a href="{LINK}" class="link">{DIR_NAME}</a></td>
					<td width="60" class="content" style="white-space:nowrap;" align="center">
						<!-- BDP: action_link -->
						<a href="javascript:CopyText('{CHOOSE_IT}');" class="link">{CHOOSE}</a>
						<input type="hidden" name="{CHOOSE_IT}" value="{CHOOSE_IT}" />
						<!-- EDP: action_link -->
					</td>
					<!-- EDP: list_item -->
				</tr>
				<!-- EDP: dir_item -->
			</table>
		</form>
	</body>
</html>
