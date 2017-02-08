jQuery(document).ready(function($) {

	if($.fn.twipsy) {
		$('a[rel="twipsy"]').twipsy();
		$('a[rel="popover"]').popover({trigger: 'hover'}).click(function(event) {event.preventDefault();});
	}
	
	var scribe_custom_thickbox_width = 1050;
	var scribe_term_select_update = false;
	var scribe_refresh_post_screen = false;
	var scribe_in_ajax_call = false;
	
	var old_tb_position = tb_position;
	tb_position = function() {
		if(scribe_seo.custom_tb) {
			var tbWindow = $('#TB_window'), width = $(window).width(), H = $(window).height(), W = ( scribe_custom_thickbox_width < width ) ? scribe_custom_thickbox_width : width, adminbar_height = 0;

			if ( $('body.admin-bar').length )
				adminbar_height = 28;
	
			if ( tbWindow.size() ) {
				tbWindow.width( W - 50 ).height( H - 45 - adminbar_height );
				$('#TB_iframeContent').width( W - 50 ).height( H - 75 - adminbar_height );
				tbWindow.css({'margin-left': '-' + parseInt((( W - 50 ) / 2),10) + 'px'});
				if ( typeof document.body.style.maxWidth != 'undefined' )
					tbWindow.css({'top': 20 + adminbar_height + 'px','margin-top':'0'});
			};
	
			return $('a.thickbox').each( function() {
				var href = $(this).attr('href');
				if ( ! href ) return;
				href = href.replace(/&width=[0-9]+/g, '');
				href = href.replace(/&height=[0-9]+/g, '');
				$(this).attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 - adminbar_height ) );
			});
		} else {
			old_tb_position();
		}
	};
	
	var old_tb_remove = tb_remove;
	tb_remove = function() {
		scribe_seo.custom_tb = false;
		old_tb_remove();

		if (scribe_refresh_post_screen)
			$('#save').click();

		scribe_refresh_post_screen = false;
		scribe_in_ajax_call = false;
	};

	var old_link_building_term_alt = $('.scribe-link-building-research-button').attr('alt');
	$('.scribe-link-building-meta-box-results .button').click(function(event) {
		if (scribe_in_ajax_call)
			return;
		scribe_in_ajax_call = true;

		index = old_link_building_term_alt.indexOf('&TB_i');
		if (index > 0) {
			alt = old_link_building_term_alt.substr(0,index) + '&scribe-link-building-term=' + $('#scribe-link-building-term').val() + old_link_building_term_alt.substr(index);
			$('.scribe-link-building-research-button').attr('alt',alt);
		}
//		scribe_seo.custom_tb = true;
	});
	
	$('.scribe-document-analysis-search-result-preview-toggle a').click(function(event) {
		event.preventDefault();
		if (scribe_in_ajax_call)
			return;
		scribe_in_ajax_call = true;

		var $this = $(this);
		var rel = $this.attr('rel');
		
		var $containers = $('.scribe-document-analysis-search-result-preview-container').hide().filter('.scribe-document-analysis-search-result-preview-container-'+rel).show();
		var $links = $('.scribe-document-analysis-search-result-preview-toggle').show().filter('[rel="'+rel+'"]').hide();
	}).filter(':first').click();

	$('.scribe-post-tag').click(function(){
		$('#scribe-tags-added-message').hide();
	});

	$('#scribe-add-post-tags').click(function(event) {
		event.preventDefault();
		
		var tags = [];
		$('input[type="checkbox"].scribe-post-tag:checked').each(function() {
			var $this = $(this);
			if ($this.attr('disabled'))
				return;

			$this.attr('disabled', 'disabled');
			
			tags.push($this.val());
		});
		if (tags.length) {
			top.scribe_seo.add_tags_to_post(tags);
			$('#scribe-tags-added-message').show();
		}
	});
	
	if(typeof top != 'undefined' && typeof top.scribe_seo != 'undefined') {
		var existing_post_tags = top.scribe_seo.get_tags_for_post();
		if(0 < existing_post_tags.length) {
			$('input[type="checkbox"].scribe-post-tag').each(function() {
				var $this = $(this);
				var value = $.trim($this.val()).toLowerCase();
				
				if(-1 < $.inArray(value, existing_post_tags)) {
					$this.attr('checked', 'checked');
					$this.attr('disabled', 'disabled');
				}
			});
		}
	}
	
	$('.scribe-default').focus(function() {
		var $this = $(this);
		var value = $.trim($this.val());
		var default_value = $this.attr('data-default');
		
		if(default_value == value) {
			$this.val('');
		}
	}).blur(function() {
		var $this = $(this);
		var value = $this.val();
		var default_value = $this.attr('data-default');
		
		if('' == value) {
			$this.val(default_value);
		}
	}).blur();
	
	$('.scribe-thickbox-close').click(function(event) {
		event.preventDefault();
		
		top.tb_remove();
	});
	
	/// This is where we set up the stuff on the post page
	if($('#scribe-analysis').size() > 0) {
		
		/// CONTENT ANALYSIS
		
		if('' != Scribe_SEO_Configuration.seo_title_id && '' != Scribe_SEO_Configuration.seo_description_id) {
			// For those cases where the SEO inputs don't have IDs and just have names, we do this to add the ID
			$('[name="'+Scribe_SEO_Configuration.seo_title_id+'"], [name="'+Scribe_SEO_Configuration.seo_description_id+'"]').each(function() {
				var $this = $(this);
				if('' == $.trim($this.attr('id'))) {
					$this.attr('id', $this.attr('name'));
				}
			});
	
			// Set up dependency checking
			$.each(['content',Scribe_SEO_Configuration.seo_title_id,Scribe_SEO_Configuration.seo_description_id],function(index,value){
				if ($('#' + value).length) {
					$('#' + value).blur(function() {
						scribe_seo.check_dependencies();
					});
				} else if ($('[name="'+value+'"]').length) {
					$('[name="'+value+'"]').blur(function() {
						scribe_seo.check_dependencies();
					});
				}
			});
			scribe_seo.check_dependencies();
		}
		
		$('#scribe-content-analysis-analyze-button').click(function(event) {
			event.preventDefault();
			if (scribe_in_ajax_call)
				return;
			scribe_in_ajax_call = true;
			
			var title = scribe_seo.get_dependency_value('title');
			var description = scribe_seo.get_dependency_value('description');
			var content = scribe_seo.get_dependency_value('content');
			var post_id = $('#post_ID').val();
			var headline = $('#title').val(); 

			scribe_seo.analyze_content(title, description, content, post_id, headline, function(data,status) {
				if(!data.error) {
					scribe_seo.set_content_analysis_score(data.content_analysis.best_primary_score);
					scribe_seo.set_content_analysis_evals_remaining(data.evaluations_remaining);
					scribe_seo.setup();
					scribe_refresh_post_screen = true;
					
					var show_url = $.trim($('#scribe-content-analysis-review-button').attr('alt'));
					tb_show(Scribe_SEO_Configuration.content_analysis_label, show_url, null);
				}
			});
		});
		
		/// KEYWORD RESEARCH
		
		$('#scribe-keyword-research-term').autocomplete({
			appendTo: '#scribe-keyword-research-meta-box-input',
			delay: 150,
			html: true,
			minLength: 1,
			source: Scribe_SEO_Configuration.autocomplete_url
		});

		var scribe_keyword_research_autosuggest_index = 0;
		$('#scribe-keyword-research-term').keyup(function(event){
			if (event.keyCode == $.ui.keyCode.UP && scribe_keyword_research_autosuggest_index > 0) {
				scribe_keyword_research_autosuggest_index--;
			} else if (event.keyCode == $.ui.keyCode.DOWN && scribe_keyword_research_autosuggest_index < $('#scribe-keyword-research-meta-box-input > ul.ui-autocomplete > li').length) {
				scribe_keyword_research_autosuggest_index++;
			} else {
				scribe_keyword_research_autosuggest_index = 0;
			}
			$('#scribe-keyword-research-meta-box-input > ul.ui-autocomplete > li').removeClass('current');
			if (scribe_keyword_research_autosuggest_index > 0)
			$('#scribe-keyword-research-meta-box-input > ul.ui-autocomplete li:nth-child(' + scribe_keyword_research_autosuggest_index + ')').addClass('current');
		});
		function scribe_seo_handle_keyword_research_event(event) {
			event.preventDefault();
			if (scribe_in_ajax_call)
				return;
			scribe_in_ajax_call = true;

			scribe_refresh_post_screen = true;
			
			var $term = $('#scribe-keyword-research-term');
			var term_value = $.trim($term.val());
			var term_value_default = $.trim($term.attr('data-default'));

			scribe_seo.research_keyword(term_value, term_value_default, function(data, status) {

				scribe_seo.set_previous_keyword_count(data.previous_suggestions.length);
				scribe_seo.set_keyword_research_evaluations_remaining(data.evaluations_remaining);
				
				var show_url = $('#scribe-keyword-research-url-placeholder').val().replace('KEYWORD_PLACEHOLDER', encodeURIComponent(data.keyword));
				tb_show(Scribe_SEO_Configuration.keyword_research_label, show_url, null);
			});
		}
		
		$('.scribe-keyword-research-search-button').click(scribe_seo_handle_keyword_research_event);
		$('#scribe-keyword-research-term').keydown(function(event) {
			if(13 == event.which) {
				scribe_seo_handle_keyword_research_event(event);
			}
		});

		$('.scribe-keyword-research-meta-box-previous-keyword-suggestions-main').click(function(event){
			event.preventDefault();
			if ($('.scribe-keyword-research-meta-box-previous-keyword-suggestions-wrap:hidden').length)
				$('.scribe-keyword-research-meta-box-previous-keyword-suggestions-wrap').slideDown();
			else
				$('.scribe-keyword-research-meta-box-previous-keyword-suggestions-wrap').slideUp();
		});
		/// LINK BUILDING
		
		scribe_seo.setup();
	}
	
	//// KEYWORD RESEARCH POP UP

	$('#scribe-keyword-suggestions-set-target').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		
		$checked = $('.scribe-keyword-suggestions-target input:checked');
		if(0 == $checked.size()) {
			alert('Please select a term you wish to target.');
		} else {
			$('#scribe-keyword-research-set-target-term-ajax-feedback').css('visibility', 'visible').show();
			$.post(
				ajaxurl,
				{
					'action': 'scribe_set_target_term',
					'scribe-set-target-term-nonce': $('#scribe-set-target-term-nonce').val(),
					'scribe-post-id': $this.attr('data-post-id'),
					'scribe-target-term': $checked.val()
				},
				function(data, status) {
					$('#scribe-keyword-research-set-target-term-ajax-feedback').css('visibility', 'hidden').hide();
					
					top.scribe_seo.set_target_term(data.target_term);
					top.scribe_seo.setup_keyword_research();
					
					$target_term_saved_notice = $('#scribe-keyword-suggestions-set-target-success').show().find('#scribe-keyword-suggestions-set-target-success-keyword').text($checked.val());
					$clear_target_term_container = $('.scribe-keyword-research-clear-target-term-container').show();
				},
				'json'
			)
		}
	});

	$('.scribe-clear-target-term').click(function(event) {
		event.preventDefault();
		$('.scribe-keyword-suggestions-target input:checked').removeAttr('checked');
		
		var $this = $(this);
		
		$.get(
			$this.attr('href'),
			{ },
			function(data, status) {
				if(typeof top == 'object' && typeof top.scribe_seo == 'object') {
					top.scribe_seo.clear_target_term();
					top.scribe_seo.setup_keyword_research();
				} else {
					scribe_seo.clear_target_term();
					scribe_seo.setup_keyword_research();
				}
				
				$clear_target_term_container = $('.scribe-keyword-research-clear-target-term-container').hide();
			},
			'json'
		);
	});
	
	$clear_target_term_container = $('.scribe-keyword-research-clear-target-term-container');		
	if($('.scribe-keyword-suggestions-target input:checked').size() > 0) {
		$clear_target_term_container.show();
	} else {
		$clear_target_term_container.hide();
	}

	/* pass select change on to other term selects */
	$('.scribe-keyword-research-headlines-term-select').change(function(event) {
		if (scribe_term_select_update || $(this).hasClass('scribe-select-new')) {
			$(this).removeClass('scribe-select-new');
			return;
		}
		value = $(this).val();
		$('.scribe-keyword-research-headlines-term-select').not(this).val(value).addClass('scribe-select-changed');
		if ($(this).hasClass('scribe-select-changed'))
			$(this).removeClass('scribe-select-changed');
	});
	
	$('#scribe-keyword-research-google-plus-headlines-term').change(function(event) {
		var $term = $('#scribe-keyword-research-google-plus-headlines-term');
		var term_value = $.trim($term.val());

		$('#scribe-keyword-suggestions-google-plus-widget-container').html('');
		$('#scribe-google-plus-ajax-feedback').css('visibility', 'visible').show();

		scribe_seo.build_links(term_value, 'soc', function(data, status) {
			$('#scribe-google-plus-ajax-feedback').hide();
			$('#scribe-keyword-suggestions-google-plus-widget-container').html(data);
		});
	});
	
	$('#scribe-keyword-research-google-insights-headlines-term').change(function(event) {
		var $selector = $(this);

		if ($selector.hasClass('js-on-load')) {
			$selector.removeClass('js-on-load').addClass('scribe-select-changed');
			return;
		}
		var $iframe = $('<iframe></iframe>');
		
		$iframe.attr('src', ajaxurl + '?action=scribe_research_google_trends&scribe-keyword=' + encodeURIComponent($selector.val()) + '&scribe-google-trends-nonce=' + $('#scribe-google-trends-nonce').val());
		$iframe.attr('width', '500').attr('height', '1190');
		
		$('#scribe-keyword-details-gtrends').empty().append($iframe);
	}).change();

	// hide the suggestions tab when it's the only tab
	if ($('#tab-scribe-keyword-suggestions').length && !$('#tab-scribe-keyword-suggestions').siblings().length)
		$('#media-upload-header').hide();

	/// LINK BUILDING POPUP

	function scribe_setup_link_building_popup(first) {
		if (typeof first == 'undefined') {
			if (scribe_in_ajax_call)
				return;

			scribe_seo.in_ajax_call = $('#scribe-link-building-research-review').val() ? false : true;
			first = false;
		}

		var $selector = $('#scribe-link-building-keywords'); 
		if ($selector) {
			if(1 >= $selector.find('option').size() && !first) { 
				$('#scribe-link-building-add-keyword').attr('disabled', 'disabled'); 
			} else { 
				$('#scribe-link-building-add-keyword').removeAttr('disabled'); 
			} 

			if(0 == $('#scribe-link-building-keyword-list li').size()) { 
				$('#scribe-link-building-keyword-list-empty').show(); 
				$('#scribe-link-building-do-link-building-research').hide(); 
			} else { 
				$('#scribe-link-building-keyword-list-empty').hide(); 
				$('#scribe-link-building-do-link-building-research').show();
			}
		}

		var $tabs = $('.scribe-link-building-tabs:not(.scribe-link-building-keyword-section)');
		if('true' == $tabs.attr('data-link-building-complete')) {
			$tabs.show();
		} else {
			$tabs.hide();
		}

	}

	function scribe_populate_link_building_popup(tab) {
		$('#scribe-build-links-ajax-feedback').css('visibility', 'visible');
		var keywords = new Array();
		if ($('.scribe-link-building-keyword-term').length) {
			$('.scribe-link-building-keyword-term').each(function() { 
				var value = $(this).text(); 
				if('' != value) { 
					keywords.push(value); 
				} 
			});
		} else {
			keywords = scribe_seo.get_site_connections_keywords();
		}

		switch(tab) {
			case 'ext':
				scribe_seo.build_links(keywords, tab, function(data, status) {
					var $texternal = $('#scribe-link-building-external-links-row-placeholder');
					var $external_table_body = $('#scribe-link-building-external-links tbody');
					$external_table_body.find('tr:not(#scribe-link-building-external-links-row-placeholder)').remove();

					$.each(data.link_analysis.externalLinks, function() {
						var $clone = $texternal.clone().removeAttr('id');

						if('' != this.url) {
							var $url = $clone.find('.scribe-link-building-external-links-row-url');
							var $da = $clone.find('.scribe-link-building-external-links-row-domain-authority');

							var $domain = this.url.replace('https://','').replace('http://','').replace(/\/.*$/,'');
							var $url_a = $('<a></a>').attr('href', this.url).text($domain).attr('target', '_blank');
							$url.html($url_a);

							var $da_a = $('<a></a>').attr('target', '_blank').attr('href', 'http://www.opensiteexplorer.org/links?site=' + encodeURI(this.url.replace('http://', '').replace('https://',''))).text(Math.round(this.pageAuthority));
							$da.html($da_a);
						}

						// NAME
						var name = '';
						if('' != this.name) {
							if('' != this.email) {
								name = '<a href="mailto:'+this.email+'">'+this.name+'</a>';
							} else {
								name = this.name;
							}
						} else if('' != this.email) {
							name = '<a href="mailto:'+this.email+'">'+this.email+'</a>';
						}

						if('' != name) {
							$clone.find('.scribe-link-building-external-links-row-contact-name').html(name);
						}

						/// ORGANIZATION
						if('' != this.organization) {
							$clone.find('.scribe-link-building-external-links-row-organization').text(' at ' + this.organization);
						}

						if('' != this.telephone) {
							$clone.find('.scribe-link-building-external-links-row-telephone').text(this.telephone);
						}
						if('' != this.numberOfPagesToUrl) {
							$clone.find('.scribe-link-building-external-links-row-links').text(this.numberOfPagesToUrl);
						}

						$external_table_body.append($clone);
					});
					$('#scribe-build-links-ajax-feedback').css('visibility', 'hidden');

					if (data.link_analysis.externalLinks.length)
						$('#scribe-link-building-external-links-row-placeholder').hide();
				});
				break;
			case 'int':
				scribe_seo.build_links(keywords, tab, function(data, status) {
					var $tinternal = $('#scribe-link-building-internal-links-row-placeholder');
					var $internal_table_body = $('#scribe-link-building-internal-links tbody');
					$internal_table_body.find('tr:not(#scribe-link-building-internal-links-row-placeholder,#scribe-link-building-internal-links-row-none)').remove();
					$('.scribe-link-building-tabs').attr('data-link-building-complete', 'true').show();
					$('#scribe-link-building-internal-links-row-none').show();
					$('#scribe-build-links-ajax-feedback').css('visibility', 'hidden');

					if (!data.link_analysis.internalLinks.length)
						return;

					var internal_keywords = encodeURIComponent(data.link_analysis.keywords.join(','));

					$.each(data.link_analysis.internalLinks, function() {
						var $clone = $tinternal.clone().removeAttr('id');
						var $link = $clone.find('.scribe-link-building-internal-links-row-page-title a');
						var href = $link.attr('href').replace('XXXX', internal_keywords).replace('YYYY', encodeURIComponent(this.url.replace('http://','').replace('https://','')));
						$link.attr('href', href).html(this.pageTitle);

						$clone.find('.scribe-link-building-internal-links-row-page-authority').text(Math.round(this.pageAuthority));
						var uri_count = new Array();
						var uri_string = new String(this.numberOfPagesToUrl);
						while (uri_string.length) {
							uri_count.unshift(uri_string.slice(-3));
							uri_string = uri_string.slice(0, -3);
						}
						$clone.find('.scribe-link-building-internal-links-row-page-links').text(uri_count.join(','));
						$internal_table_body.append($clone);
						$('#scribe-link-building-internal-links-row-none').hide();
					});
				});
				break;
			case 'soc':
				scribe_seo.build_links(keywords, 'soc', function(data, status) {
					$('#scribe-keyword-suggestions-google-plus-widget-container').html(data);
					$('#scribe-build-links-ajax-feedback').css('visibility', 'hidden');
				});
				break;
		};
	}
	$('#scribe-link-building-add-keyword').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var $selector = $('#scribe-link-building-keywords');
		var value = $selector.val();
		
		if('' == value) {
			alert('Please select a keyword.');
			return;
		}
		
		$selector.find('option:selected').remove();
		$selector.find('option[value=""]').attr('selected', 'selected');
		
		var $clone = $('#scribe-link-building-keyword-list-template').clone().removeAttr('id');
		$clone.find('.scribe-link-building-keyword-term').text(value);
		$clone.appendTo('#scribe-link-building-keyword-list');
		
		scribe_setup_link_building_popup();
	});
	
	$('.scribe-link-building-keyword-list-remove').live('click', function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var $li = $this.parents('li');
		
		var $selector = $('#scribe-link-building-keywords');
		var value = $li.find('.scribe-link-building-keyword-term').text();
		$li.remove();
		var $option = $('<option></option>').val(value).text(value);
		$selector.append($option);
		
		scribe_setup_link_building_popup();
	});

	scribe_setup_link_building_popup(true);

	$('.scribe-link-building-tabs-identifiers a').click(function(event) {
		event.preventDefault();
		
		var $this = $(this);
		var href = $this.attr('href');
		$('.scribe-link-building-tab-section').hide().filter(href).show();
		$('.scribe-link-building-tabs-identifiers a').removeClass('nav-tab-active').filter(href+'-tab-identifier').addClass('nav-tab-active');
		if ($(href + '-headlines-term').hasClass('scribe-select-changed')) {
			scribe_term_select_update = true;
			$(href + '-headlines-term').change();
			scribe_term_select_update = false;
		}

		if ($this.attr('data-load')) {
			scribe_populate_link_building_popup($this.attr('data-load'));
			$this.attr('data-load','');
		}
	}).filter('#scribe-link-building-difficulty-tab-identifier, .nav-tab-active').click();
	
	$('#scribe-link-building-do-link-building-research').click(function(event) {
		event.preventDefault();

		scribe_populate_link_building_popup('int');
	});
	$('#scribe-link-building-do-site-connections-research').click(function(event) {
		event.preventDefault();

		var keywords = scribe_seo.get_site_connections_keywords();
		if ( !keywords.length )
			return;
		
		$('#scribe-build-links-ajax-feedback').css('visibility', 'visible');
		scribe_seo.build_links(keywords, 'scr', function(data, status) {
			$('.scribe-link-building-tabs,.scribe-link-building-tabs-identifiers').show();

			score = $.isNumeric(data.link_analysis.linkScore) ? Math.round(data.link_analysis.linkScore) : 0;
			score_letter = score < 26 ? 'A' : (score < 51 ? 'B' : (score < 76 ? 'C' : 'D'));
			$('.scribe-link-building-tab-difficulty-header').text(data.link_analysis.keywords.join(' + '));
			$('.scribe-link-building-tab-difficulty-score').removeClass('scribe-graph-green scribe-graph-red').addClass(50 > score ? 'scribe-graph-green' : 'scribe-graph-red');
			$('.scribe-link-building-tab-difficulty-score span').text(score_letter);
			$('.scribe-link-building-tab-difficulty-score-text').html(data.link_analysis.score_description);
			$('#scribe-build-links-ajax-feedback').css('visibility', 'hidden');
			$('#scribe-link-building-external-links-tab-identifier').attr('data-load','ext');
			$('#scribe-link-building-social-media-tab-identifier').attr('data-load','soc');
			$('#scribe-link-building-external-links tbody').find('tr:not(#scribe-link-building-external-links-row-placeholder)').remove();
			$('#scribe-link-building-external-links-row-placeholder').show();
		});
	});

	if ($('#scribe-link-building-keyword-list .scribe-link-building-added-keyword').length)
		$('#scribe-link-building-do-link-building-research').click();

	// hide all the tips initially
	$('.scribe-link-building-tip').hide();
	$('.scribe-show-tip').click(function(event){
		event.preventDefault();
		$(this).hide().siblings('.scribe-link-building-tip').show();
	});
	// show site connection tips
	$('.scribe-metaboxes .scribe-show-tip').click();

	$('.scribe-hide-tip').click(function(event){
		event.preventDefault();
		$(this).hide().siblings('.scribe-link-building-tip').hide();
		$('.scribe-show-tip').show();
	});

	// remove the WordPress SEO title placeholder to prevent confusion for Scribe users
	if ($('#yoast_wpseo_title').length && typeof( updateTitle ) == 'function' ) {
		var wpseo_updateTitle = updateTitle;

		updateTitle = function(force) {

			wpseo_updateTitle(force);
			$('#yoast_wpseo_title').attr('placeholder','');

		}
	}
});

var scribe_seo = function() {
	var object = {};
	var $ = jQuery;
	
	// DEPENDENCY (PREMISE SHOULD MODIFY THIS)
	object.dependency_map = {'title': Scribe_SEO_Configuration.seo_title_id, 'description': Scribe_SEO_Configuration.seo_description_id, 'content': Scribe_SEO_Configuration.content_id};
	
	// STATE FLAGS
	object.content_analyzing = false;
	object.keyword_researching = false;
	object.link_building = false;
	
	/// UTILITY
	object.custom_tb = false;
	
	object.add_tags_to_post = function(tags) {
		var $tag_input = $('#new-tag-post_tag');
		var existing_value = $tag_input.val();
		jQuery('#new-tag-post_tag').val(existing_value + ', ' + tags.join(', '));
		
		$tag_input.parent().find('.tagadd').click();
	};
	
	object.get_tags_for_post = function() {
		var tags = new Array();
		var $tags = $('#post_tag .tagchecklist span');
		
		$tags.each(function() {
			var $clone = $(this).clone();
			$clone.find('a').remove();
			tags.push($.trim($clone.text()).toLowerCase());
		});
		
		return tags;
	};
	
	/// SETUP
	
	object.setup = function() {
		object.setup_content_analysis();
		object.setup_keyword_research();
		object.setup_link_building();
	};
	
	object.setup_content_analysis = function() {
		var score = scribe_seo.get_content_analysis_score();
		var $review = $('#scribe-content-analysis-review-button');
		if('' == score) {
			$review.attr('disabled', 'disabled');
			$review.hide();
		} else {
			$review.removeAttr('disabled');
			$review.show();
		}
	};

	object.setup_keyword_research = function() {
		var $previous_keyword_suggestions = $('.scribe-keyword-research-meta-box-previous-keyword-suggestions');
		var count = $previous_keyword_suggestions.attr('data-previous-count');
		
		if(count > 0) { 
			$previous_keyword_suggestions.show();
		} else {
			$previous_keyword_suggestions.hide();
		}
		
		var $keyword_research_input_container = $('.scribe-keyword-research-meta-box-input');
		var $target_term_container = $('.scribe-keyword-research-meta-box-target');
		var $target_term_text_container = $target_term_container.find('.scribe-keyword-research-meta-box-target-text');
		
		var target_term = $.trim($target_term_text_container.text());
		
		if('' == target_term) {
			$keyword_research_input_container.css('border-bottom-style', 'none');
			$target_term_container.hide();
		} else {
			$keyword_research_input_container.css('border-bottom','solid 1px #e5e5e5');
			$target_term_container.show();
		}
	};
	
	object.setup_link_building = function() {
		var $twitter_connect = $('#scribe-link-building-meta-box-twitter-connect');
		var twitter_screen_name = $.trim($twitter_connect.attr('data-screen-name'));
		if('' != twitter_screen_name) {
			$twitter_connect.hide();
		}
		
		var $connectors = $('.scribe-link-building-meta-box-connect a');
		var $visible_connectors = $connectors.filter(':visible');
		if(0 < $visible_connectors.size()) {
			$('.scribe-link-building-meta-box-section:not(.scribe-link-building-meta-box-connect)').hide();
			return;
		} else {
			$('.scribe-link-building-meta-box-connect').hide();
		}
		
		var $content_analysis_dependency = $('.scribe-link-building-dependency-content-analysis');
		var $link_building_term = $('.scribe-link-building-link-term');
		link_building_terms = object.get_link_building_terms();
		
		var $research_button_container = $('.scribe-link-building-research-button-container');
		var $research_button = $research_button_container.find('input[type="button"]');
		var content_score = $.trim($research_button.attr('data-content-analysis-score'));

		var $review_button_container = $('.scribe-link-building-review-button-container');
		var $review_button = $('.scribe-link-building-review-button');

		$content_analysis_dependency.hide();
		$link_building_term.hide();
		$research_button_container.hide();
		$review_button_container.hide();

		if('' == content_score) {
			$content_analysis_dependency.filter(':not(.scribe-ready)').show();
		} else {
			$research_button_container.show();
			$content_analysis_dependency.filter('.scribe-ready').show();
			
			if(0 < link_building_terms.length) {
				$link_building_term.show();
				$review_button_container.show();
			}
		}
	};
	
	/// CONTENT ANALYSIS
	
	object.analyze_content = function(title, description, content, post_id, headline, callback) {
		$('#scribe-analysis-is-analyzing').css('visibility', 'visible');

		$.post(
			ajaxurl,
			{
				'action': 'scribe_analyze_content',
				'scribe-analyze-content-nonce': $('#scribe-analyze-content-nonce').val(),
				'scribe-content': content,
				'scribe-description': description,
				'scribe-title': title,
				'scribe-post-id': post_id,
				'scribe-headline': headline
			},
			function(data, status) {
				$('#scribe-analysis-is-analyzing').css('visibility', 'hidden');
				
				if(data.error) {
					alert(data.error_message);
				} else {
					if(typeof callback == 'function') {
						callback(data, status);
					}
				}
			},
			'json'
		);
	};
	
	object.get_content_analysis_score = function() {
		return $.trim($('.scribe-content-analysis-score:first').attr('data-content-analysis-score'));
	};
	
	object.set_content_analysis_score = function(score) {
		$('.scribe-content-analysis-score').attr('data-content-analysis-score', score);
	};
	
	object.set_content_analysis_evals_remaining = function(evals) {
		$('.scribe-analysis-evaluations-remaining').text(evals);
	};
	
	object.set_content_analysis_keywords = function(keywords) {
		$('.scribe-analysis-meta-box-keywords').text(terms.join(' + '));
	};
	
	//// DEPENDENCIES
	
	object.check_dependencies = function() {
		var ready_for_analysis = true;
		for(var dependency_name in object.dependency_map) {
			var dependency_value = object.get_dependency_value(dependency_name);

			var $indicators = $('.scribe-analysis-dependency-'+dependency_name).hide();
			if('' == dependency_value) {
				ready_for_analysis = false;
				$indicators.filter(':not(.scribe-ready)').show();
			} else {
				$indicators.filter('.scribe-ready').show();
			}
		}
		
		var $analyze_button = $('.scribe-analysis-meta-box-analyze-action .button');
		if(ready_for_analysis) {
			$analyze_button.removeAttr('disabled');
		} else {
			$analyze_button.attr('disabled', 'disabled');
		}
	};
	
	object.get_dependency_value = function(dependency_name) {
		var dependency_input_id = object.dependency_map[dependency_name];

		if('content' == dependency_input_id && typeof(tinyMCE) != 'undefined' && tinyMCE.get('content') && !tinyMCE.get('content').isHidden()) {
			var dependency_value = tinyMCE.get('content').getContent().replace('<br />','');
		} else if ( $('#'+dependency_input_id).length ) {
			var dependency_value = $('#'+dependency_input_id).val();
		} else {
			var dependency_value = $('[name="'+dependency_input_id+'"]').val();			
		}

		dependency_value =  $.trim(dependency_value);

		return dependency_value;
	};
	
	/// KEYWORD RESEARCH

	object.research_keyword = function(keyword, keyword_default, callback) {
		if('' == keyword || keyword_default == keyword) {
			alert('Please enter a keyword to search for.');
		}
		
		$('#scribe-keyword-research-ajax-feedback').css('visibility', 'visible');
		
		$.post(
			ajaxurl,
			{
				'action': 'scribe_research_keyword',
				'scribe-research-keyword-nonce': $('#scribe-research-keyword-nonce').val(),
				'scribe-post-id': $('#post_ID').val(),
				'scribe-keyword': keyword
			},
			function(data, status) {
				$('#scribe-keyword-research-ajax-feedback').css('visibility', 'hidden');
				
				if(data.error) {
					alert(data.error_message);
				} else {
					if(typeof callback == 'function') {
						callback(data, status);
					}
				}
			},
			'json'
		);
	};

	object.set_keyword_research_evaluations_remaining = function(evals) {
		$('.scribe-keyword-research-evaluations-remaining').text(evals);
	};

	object.set_previous_keyword_count = function(count) {
		$('.scribe-keyword-research-meta-box-previous-keyword-suggestions').attr('data-previous-count', count);
	};

	//// TARGET TERM

	object.clear_target_term = function() {
		$('.scribe-keyword-research-meta-box-target-text').text('');
	};

	object.set_target_term = function(target_term) {
		$('.scribe-keyword-research-meta-box-target-text').text(target_term);
	};

	/// LINK BUILDING

	object.build_links = function(keywords, type, callback) {
		$.post(
			ajaxurl,
			{
				'action': 'scribe_build_links',
				'scribe-build-links-nonce': $('#scribe-build-links-nonce').val(),
				'scribe-post-id': $('#post_ID').val(),
				'scribe-keywords': keywords,
				'type': type
			},
			function(data, status) {

				if(data.error) {
					alert(data.error_message);
				} else {
					if(typeof callback == 'function') {
						callback(data, status);
					}
				}
			},
			'json'
		);
	};

	object.get_link_building_terms = function() {
		var $link_building_term_text = $('#scribe-link-building-term-list');
		var link_building_terms = $.grep($.trim($link_building_term_text.val()).split(' + '), function(n, i) {return $.trim(n) != '';});
		return link_building_terms;
	};
	object.get_site_connections_keywords = function() {
		var keywords = $('#scribe-site-connections-keywords').val().split(',');
		return $.grep(keywords, function(x){ return ($.trim(x)); });		
	};
	
	return object;
}();

function scribe_tiny_mce_add_change_callback(ed) {

	// check for TinyMCE 3.x
	if ( typeof ed.on == 'function' ) {

		ed.on('keyup', function(ed, e) {
			scribe_seo.check_dependencies();
		});

	} else {

		ed.onKeyUp.add( function(ed, e) {
			scribe_seo.check_dependencies();
		});

	}
}

function scribe_keyword_analysis_plot() {

	var options = {
		target: {
			backgroundColor: "transparent"
		},
		title: {
			text: '',
			show: false
		},
		axesDefaults: {
			min: -1,
			max: 1,
			show: false,
			showTicks: false,
			showTickMarks: false,
			pad: .01,
			tickOptions: {
				mark: 'cross',
				showMark: false,
				showGridline: false,
				markSize: 0,
				show: false,
				showLabel: false
			}
		},
		axes: {
			xaxis: {
				min:0,
				max:5
			},
			yaxis: {
				min:0,
				max:10
			}
		},
		seriesDefaults: {
			showLine: false,
			showMarker: true,
			pointLabels: {
				escapeHTML: false,
				show: true
			},
			markerOptions: {
				color: '#df6900',
				show: true,
				style: 'filledCircle'
			}
		},
		grid: {
			drawGridLines: false,
			gridLineColor: "transparent",
			gridLineWidth: 0,
			backgroundColor: "transparent",
			borderColor: "transparent",
			borderWidth: 0,
			shadow: false
		}
	};
	
	jQuery('.scribe-keyword-analysis-details-table tbody tr').each(function(index, element) {
		var $row = jQuery(element);
		var kwod = $row.attr('data-kwod');
		var x = parseFloat($row.attr('data-x'));
		var y = parseFloat($row.attr('data-y'));
		var padding = parseFloat($row.attr('data-pad'));
		var point_class = 'scribe-point-' + index;
		var point = [[x, y, '<span class="' + point_class + '">' + $row.find('.scribe-keyword-analysis-details-table-keyword a').text() + '</span>']];
		var placement = x > 4.1 ? 'left' : (y > 9 ? 'below' : 'above');

		if (y > 9.5) {
			if (x > 4.75)
				options.seriesDefaults.pointLabels.location = 'sw';
			else
				options.seriesDefaults.pointLabels.location = 's';
		} else if (y > 9.1) {
			if (x > 4.1)
				options.seriesDefaults.pointLabels.location = padding ? 'sw' : 'w';
			else
				options.seriesDefaults.pointLabels.location = padding ? 'se' : 'e';
		} else {
			if (x > 4.75)
				options.seriesDefaults.pointLabels.location = 'nw';
			else
				options.seriesDefaults.pointLabels.location = 'n';
		}
		options.seriesDefaults.pointLabels.ypadding = 9 + (padding * 18);

		var keyword_plot = jQuery.jqplot('scribe-keyword-analysis-graph-container', [point], options);
		var $point_label = jQuery('.jqplot-point-label .'+point_class);
		$point_label.attr('title', kwod).twipsy({ placement: placement });
	});
}
