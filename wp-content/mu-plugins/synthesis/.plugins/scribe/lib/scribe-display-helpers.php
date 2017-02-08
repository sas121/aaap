<?php

function scribe_score_class( $score, $type = 'doc' ) {

	if ( $type == 'site' && 70 <= $score )
		return 'scribe-great';

	elseif ( $type == 'doc' && 70 <= $score )
		return 'scribe-great';

	elseif ( $type == 'dificulty' && 5 < $score )
		return 'scribe-great';

	return 'scribe-average';

}

function scribe_bar_graph_class( $score, $type = '' ) {

	if ( 'difficulty' == $type || 'keyword-difficulty' == $type )
		return 50 > $score ? 'scribe-graph-green' : 'scribe-graph-red';

	if ( 'popularity' == $type ) {

		if ( 70 <= $score )
			return 'scribe-graph-green';

		if ( 40 <= $score )
			return 'scribe-graph-yellow';

		return 'scribe-graph-red';

	}

	if ( 'competition' == $type ) {

		if ( 50 > $score )
			return 'scribe-graph-green';

		if ( 71 > $score )
			return 'scribe-graph-yellow';

		return 'scribe-graph-red';

	}

	if (80 <= $score) {
		$class = 'scribe-graph-green';
	} elseif (20 <= $score) {
		$class = 'scribe-graph-yellow';
	} else {
		$class = 'scribe-graph-red';
	}

	return $class;
}

function scribe_get_content_for_letter_score($score, $a, $b, $c, $d) {
	$score = strtolower($score);
	if (in_array($score, array('a', 'b', 'c', 'd'))) {
		return $$score;
	} else {
		return '';
	}
}

function scribe_get_keyword_analysis_score_text($score) {
	return apply_filters( 'scribe_get_keyword_analysis_score_text', 
		scribe_get_content_for_letter_score(
			$score,
			__( 'Congratulations, this term strikes a good balance between search optimization and copywriting best practices.', 'scribeseo' ),
			__( 'You\'re close! This keyword requires a bit more work to reach the correct balance between search optimization and copywriting best practices.', 'scribeseo' ),
			__( 'You need to integrate this keyword more closely into your copy to achieve a better score and make it more relevant.', 'scribeseo' ),
			__( 'Please reevaluate your use of this keyword to improve its ranking.', 'scribeseo' )
		)
	);
}

function scribe_get_content_for_score( $score, $a, $b, $c, $type = '' ) {

	if ( 'overall' == $type )
		return 6 > $score ? $a : $b;

	if ( 100 == $score )
		return $a;
	elseif ( 100 > $score )
		return $b;

	return $c;

}

function scribe_get_keyword_analysis_data_y( $kws ) {

	$kws = (float) $kws;
	if ( $kws <= 0.15 )
		return 0.15;

	if ( $kws <= 5.0 )
		return $kws;

	return ( ( min( $kws, 11 ) - 5 ) * 5 / 6.15 ) + 5;
}

function scribe_get_content_analyis_site_score_text( $score, $keywords ) {

	if ( $score < 50 ) {

		$text = __( 'While terms like <strong>%1$s</strong> and <strong>%2$s</strong> appear in your page, your site does not use these terms frequently. Consider adding more content to your site for these terms. %3$s', 'scribeseo' );
		$improve_text = '';

	} else {

		$text = __( 'This page is a good match for your site for terms like <strong>%1$s</strong> and <strong>%2$s</strong>. %3$s', 'scribeseo' );
		$improve_text = __( 'To improve your Site Score, consider adding more content related to these terms.', 'scribeseo' );

	}

	// find the top kws & primary keywords
	$kws = array();
	$primary_keywords = array();
	foreach( $keywords as $index => $keyword ) {
		if ( ! isset( $kws[$keyword->kws] ) )
			$kws[$keyword->kws] = array();

		$kws[$keyword->kws][] = $index;

		if ( $keyword->kwl == 1 )
			$primary_keywords[] = esc_html( $keyword->text );

	}
	krsort( $kws, SORT_NUMERIC );

	$terms = array();
	$k = 1;
	$primary = false;
	while( $k < 3 && ! empty( $kws ) ) {

		$top_kws = array_shift( $kws );
		while( $k < 3 && ! empty( $top_kws ) ) {

			$index = array_shift( $top_kws );
			$terms[$k] = $keywords[$index]->text;
			$primary |= ( $keywords[$index]->kwl == 1 );
			$k++;

		}
	}

	reset( $kws );
	$keyword_list = implode( __( '</strong> and <strong>', 'scribeseo' ), $primary_keywords );

	if ( ! empty( $primary_keywords ) ) {

		if ( $score < 70 && key( $kws ) < 5 )
			$text = sprintf( __( 'While the terms <strong>%s</strong> are used in your page, they are not used frequently enough on your site. Consider adding more content to your site for these terms.', 'scribeseo' ), $keyword_list );
		elseif ( ! $primary )
			$improve_text = sprintf( __( ' To improve your Site Score, consider adding more content related to <strong>%s</strong>, so that this page better aligns with your site.', 'scribeseo' ), $keyword_list );

	}

	return sprintf( $text, esc_html( $terms[1] ), esc_html( $terms[2] ), $improve_text );

}

function scribe_get_content_analysis_doc_score_text( $score, $keywords ) {

	if ( $score < 100 )
		return __( 'Your page needs some improvement. Review the suggestions under Improve Page Structure to raise your score.', 'scribeseo' );

	if ( $score == 100 ) {

		$primary = array();
		$primary_keywords = array();
		foreach( $keywords as $index => $keyword ) {

			if ( $keyword->kwl == 1 ) {

				// make the array key a string instead of a number
				$primary['x' . $index] = $keyword->kwc;
				$primary_keywords[$index] = esc_html( $keyword->text );

			}

		}
		if ( count( $primary_keywords > 2 ) ) {

			arsort( $primary, SORT_NUMERIC );
			$primary = array_slice( $primary, 0, 2 );
			$primary_keys = array_keys( $primary );
			foreach( $primary_keywords as $index => $keyword ) {

				if ( ! in_array( 'x'.$index, $primary_keys ) )
					unset( $primary_keywords[$index] );

			}
		}

		return sprintf( __( 'Your page does a good job of meeting the best practices of SEO Copywriting for the term <strong>%s</strong>.', 'scribeseo' ), implode( __( '</strong> and <strong>', 'scribeseo' ), $primary_keywords ) );
	}

	return __( 'Your page does a good job of meeting the best practices of SEO Copywriting.', 'scribeseo' );

}

function scribe_get_link_building_tab_load( $link_building_info, $tab ) {

	$load = false;
	foreach( array( 'ext' => 'externalLinks', 'int' => 'internalLinks', 'soc' => 'googlePlusActivities' ) as $type => $data ) {

		if ( $tab != $type )
			continue;
		if ( ! isset( $link_building_info->$data ) )
			$load = true;

		break;
	}

	return $load ? 'data-load="' . esc_attr( $tab ) . '"' : '';

}
function scribe_get_link_building_score_description($score) {
	if ( $score > 50 )
		return apply_filters('scribe_get_link_building_score_description', __( 'Getting connections to your site for the term <strong>%s</strong> will be difficult. <a href="http://www.copyblogger.com/content-marketing" target="_blank">Click here</a> to study a few useful tutorials that will help you write the kind of content your audience is looking for. ', 'scribeseo' ) );

	return apply_filters('scribe_get_link_building_score_description',
		scribe_get_content_for_score(
			$score,
			__( 'You have plenty of connections for the phrase you\'ve chosen.', 'scribeseo' ),
			__( 'You have a lot of connections, but need more for this phrase.', 'scribeseo' ),
			__( 'You need to generate a lot more connections for this phrase.', 'scribeseo' ),
			'link'
		)
	);
}

function scribe_get_overall_score_description($score) {
	return apply_filters( 'scribe_get_overall_score_description',
		scribe_get_content_for_score(
			$score,
			__( 'It will not be difficult for you to rank on a search engine for this keyword.', 'scribeseo' ),
			__( 'This term will be somewhat difficult for you to rank for on a search engine.', 'scribeseo' ),
			'',
			'overall'
		)
	);
}

function scribe_the_overall_score_description($score) {
	echo apply_filters('scribe_the_overall_score_description', scribe_get_overall_score_description($score), $score);
}

function scribe_get_content_score_description($score) {
	return apply_filters( 'scribe_get_content_score_description',
		scribe_get_content_for_score(
			$score,
			__( 'Great! You have an awesome score!', 'scribeseo'),
			__( 'Consider adding more content to your site for this term.', 'scribeseo' ),
			__( 'Your site exceeds the amount of content for this term for sites that are ranking.', 'scribeseo' )
		)
	);
}

function scribe_the_content_score_description($score) {
	echo apply_filters('scribe_the_content_score_description', scribe_get_content_score_description($score), $score);
}

function scribe_get_content_score_more_link($score) {
	return '#';
}

function scribe_get_link_score_description($score) {
	return apply_filters( 'scribe_get_link_score_description',
		scribe_get_content_for_score(
			$score,
			__( 'Great! You have an awesome score!', 'scribeseo' ),
			__( 'You will need to obtain more links to your site for this term.', 'scribeseo' ),
			__( 'Your site exceeds the number of links for sites that currently rank for this term.', 'scribeseo' )
		)
	);
}

function scribe_the_link_score_description($score) {
	echo apply_filters('scribe_the_link_score_description', scribe_get_link_score_description($score), $score);
}

function scribe_get_link_score_more_link($score) {
	return '#';
}

function scribe_get_domain_authority_score_description($score) {
	return apply_filters( 'scribe_get_domain_authority_score_description',
		scribe_get_content_for_score(
			$score,
			__( 'Great! You have an awesome score!', 'scribeseo' ),
			__( 'You should obtain more links for this term from sites that have strong authority.', 'scribeseo' ),
			__( 'Your site’s authority exceeds the authority of most sites that rank for this term.', 'scribeseo' )
		)
	);
}

function scribe_the_domain_authority_score_description($score) {
	echo apply_filters('scribe_the_domain_authority_score_description', scribe_get_domain_authority_score_description($score), $score);
}

function scribe_get_domain_authority_score_more_link($score) {
	return '#';
}

function scribe_get_facebook_likes_score_description($score) {
	return apply_filters('scribe_get_facebook_likes_score_description',
		scribe_get_content_for_score(
			$score,
			__( 'Great! You have an awesome score!', 'scribeseo' ),
			__( 'You should have more people sharing your content online in order to rank well for this term.', 'scribeseo' ),
			__( 'Your site’s content for this term is highly socialized.', 'scribeseo' )
		)
	);
}

function scribe_the_facebook_likes_score_description($score) {
	echo apply_filters('scribe_the_facebook_likes_score_description', scribe_get_facebook_likes_score_description($score), $score);
}

function scribe_get_facebook_likes_score_more_link($score) {
	return '#';
}
