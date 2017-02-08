<?php
$keywordInfo = $info['GetAnalysisResult']['Analysis']['PrimaryKeywords']['KeywordChange']['Description'];
?>
<form>
	<p><?php echo wp_kses( $keywordInfo, Scribe_SEO::formatting_allowedtags() ); ?></p>
</form>