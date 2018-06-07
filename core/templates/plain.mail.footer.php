--
<?php p($theme->getName() . ' - ' . $theme->getSlogan()); ?>
<?php
print_unescaped("\n" . $theme->getBaseUrl());
if ($theme->getImprintUrl() !== '') {
	print_unescaped("\n" . $theme->getImprintUrl());
}
if ($theme->getPrivacyPolicyUrl() !== '') {
	print_unescaped("\n" . $theme->getPrivacyPolicyUrl());
}
?>
