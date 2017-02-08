(function($){$.extend({tablesorter:new function(){var parsers=[],widgets=[];this.defaults={cssHeader:"header",cssAsc:"headerSortUp",cssDesc:"headerSortDown",sortInitialOrder:"asc",sortMultiSortKey:"shiftKey",sortForce:null,sortAppend:null,textExtraction:"simple",parsers:{},widgets:[],widgetZebra:{css:["even","odd"]},headers:{},widthFixed:false,cancelSelection:true,sortList:[],headerList:[],dateFormat:"us",decimal:'.',debug:false};function benchmark(s,d){log(s+","+(new Date().getTime()-d.getTime())+"ms");}this.benchmark=benchmark;function log(s){if(typeof console!="undefined"&&typeof console.debug!="undefined"){console.log(s);}else{alert(s);}}function buildParserCache(table,$headers){if(table.config.debug){var parsersDebug="";}var rows=table.tBodies[0].rows;if(table.tBodies[0].rows[0]){var list=[],cells=rows[0].cells,l=cells.length;for(var i=0;i<l;i++){var p=false;if($.metadata&&($($headers[i]).metadata()&&$($headers[i]).metadata().sorter)){p=getParserById($($headers[i]).metadata().sorter);}else if((table.config.headers[i]&&table.config.headers[i].sorter)){p=getParserById(table.config.headers[i].sorter);}if(!p){p=detectParserForColumn(table,cells[i]);}if(table.config.debug){parsersDebug+="column:"+i+" parser:"+p.id+"\n";}list.push(p);}}if(table.config.debug){log(parsersDebug);}return list;};function detectParserForColumn(table,node){var l=parsers.length;for(var i=1;i<l;i++){if(parsers[i].is($.trim(getElementText(table.config,node)),table,node)){return parsers[i];}}return parsers[0];}function getParserById(name){var l=parsers.length;for(var i=0;i<l;i++){if(parsers[i].id.toLowerCase()==name.toLowerCase()){return parsers[i];}}return false;}function buildCache(table){if(table.config.debug){var cacheTime=new Date();}var totalRows=(table.tBodies[0]&&table.tBodies[0].rows.length)||0,totalCells=(table.tBodies[0].rows[0]&&table.tBodies[0].rows[0].cells.length)||0,parsers=table.config.parsers,cache={row:[],normalized:[]};for(var i=0;i<totalRows;++i){var c=table.tBodies[0].rows[i],cols=[];cache.row.push($(c));for(var j=0;j<totalCells;++j){cols.push(parsers[j].format(getElementText(table.config,c.cells[j]),table,c.cells[j]));}cols.push(i);cache.normalized.push(cols);cols=null;};if(table.config.debug){benchmark("Building cache for "+totalRows+" rows:",cacheTime);}return cache;};function getElementText(config,node){if(!node)return"";var t="";if(config.textExtraction=="simple"){if(node.childNodes[0]&&node.childNodes[0].hasChildNodes()){t=node.childNodes[0].innerHTML;}else{t=node.innerHTML;}}else{if(typeof(config.textExtraction)=="function"){t=config.textExtraction(node);}else{t=$(node).text();}}return t;}function appendToTable(table,cache){if(table.config.debug){var appendTime=new Date()}var c=cache,r=c.row,n=c.normalized,totalRows=n.length,checkCell=(n[0].length-1),tableBody=$(table.tBodies[0]),rows=[];for(var i=0;i<totalRows;i++){rows.push(r[n[i][checkCell]]);if(!table.config.appender){var o=r[n[i][checkCell]];var l=o.length;for(var j=0;j<l;j++){tableBody[0].appendChild(o[j]);}}}if(table.config.appender){table.config.appender(table,rows);}rows=null;if(table.config.debug){benchmark("Rebuilt table:",appendTime);}applyWidget(table);setTimeout(function(){$(table).trigger("sortEnd");},0);};function buildHeaders(table){if(table.config.debug){var time=new Date();}var meta=($.metadata)?true:false,tableHeadersRows=[];for(var i=0;i<table.tHead.rows.length;i++){tableHeadersRows[i]=0;};$tableHeaders=$("thead th",table);$tableHeaders.each(function(index){this.count=0;this.column=index;this.order=formatSortingOrder(table.config.sortInitialOrder);if(checkHeaderMetadata(this)||checkHeaderOptions(table,index))this.sortDisabled=true;if(!this.sortDisabled){$(this).addClass(table.config.cssHeader);}table.config.headerList[index]=this;});if(table.config.debug){benchmark("Built headers:",time);log($tableHeaders);}return $tableHeaders;};function checkCellColSpan(table,rows,row){var arr=[],r=table.tHead.rows,c=r[row].cells;for(var i=0;i<c.length;i++){var cell=c[i];if(cell.colSpan>1){arr=arr.concat(checkCellColSpan(table,headerArr,row++));}else{if(table.tHead.length==1||(cell.rowSpan>1||!r[row+1])){arr.push(cell);}}}return arr;};function checkHeaderMetadata(cell){if(($.metadata)&&($(cell).metadata().sorter===false)){return true;};return false;}function checkHeaderOptions(table,i){if((table.config.headers[i])&&(table.config.headers[i].sorter===false)){return true;};return false;}function applyWidget(table){var c=table.config.widgets;var l=c.length;for(var i=0;i<l;i++){getWidgetById(c[i]).format(table);}}function getWidgetById(name){var l=widgets.length;for(var i=0;i<l;i++){if(widgets[i].id.toLowerCase()==name.toLowerCase()){return widgets[i];}}};function formatSortingOrder(v){if(typeof(v)!="Number"){i=(v.toLowerCase()=="desc")?1:0;}else{i=(v==(0||1))?v:0;}return i;}function isValueInArray(v,a){var l=a.length;for(var i=0;i<l;i++){if(a[i][0]==v){return true;}}return false;}function setHeadersCss(table,$headers,list,css){$headers.removeClass(css[0]).removeClass(css[1]);var h=[];$headers.each(function(offset){if(!this.sortDisabled){h[this.column]=$(this);}});var l=list.length;for(var i=0;i<l;i++){h[list[i][0]].addClass(css[list[i][1]]);}}function fixColumnWidth(table,$headers){var c=table.config;if(c.widthFixed){var colgroup=$('<colgroup>');$("tr:first td",table.tBodies[0]).each(function(){colgroup.append($('<col>').css('width',$(this).width()));});$(table).prepend(colgroup);};}function updateHeaderSortCount(table,sortList){var c=table.config,l=sortList.length;for(var i=0;i<l;i++){var s=sortList[i],o=c.headerList[s[0]];o.count=s[1];o.count++;}}function multisort(table,sortList,cache){if(table.config.debug){var sortTime=new Date();}var dynamicExp="var sortWrapper = function(a,b) {",l=sortList.length;for(var i=0;i<l;i++){var c=sortList[i][0];var order=sortList[i][1];var s=(getCachedSortType(table.config.parsers,c)=="text")?((order==0)?"sortText":"sortTextDesc"):((order==0)?"sortNumeric":"sortNumericDesc");var e="e"+i;dynamicExp+="var "+e+" = "+s+"(a["+c+"],b["+c+"]); ";dynamicExp+="if("+e+") { return "+e+"; } ";dynamicExp+="else { ";}var orgOrderCol=cache.normalized[0].length-1;dynamicExp+="return a["+orgOrderCol+"]-b["+orgOrderCol+"];";for(var i=0;i<l;i++){dynamicExp+="}; ";}dynamicExp+="return 0; ";dynamicExp+="}; ";eval(dynamicExp);cache.normalized.sort(sortWrapper);if(table.config.debug){benchmark("Sorting on "+sortList.toString()+" and dir "+order+" time:",sortTime);}return cache;};function sortText(a,b){return((a<b)?-1:((a>b)?1:0));};function sortTextDesc(a,b){return((b<a)?-1:((b>a)?1:0));};function sortNumeric(a,b){return a-b;};function sortNumericDesc(a,b){return b-a;};function getCachedSortType(parsers,i){return parsers[i].type;};this.construct=function(settings){return this.each(function(){if(!this.tHead||!this.tBodies)return;var $this,$document,$headers,cache,config,shiftDown=0,sortOrder;this.config={};config=$.extend(this.config,$.tablesorter.defaults,settings);$this=$(this);$headers=buildHeaders(this);this.config.parsers=buildParserCache(this,$headers);cache=buildCache(this);var sortCSS=[config.cssDesc,config.cssAsc];fixColumnWidth(this);$headers.click(function(e){$this.trigger("sortStart");var totalRows=($this[0].tBodies[0]&&$this[0].tBodies[0].rows.length)||0;if(!this.sortDisabled&&totalRows>0){var $cell=$(this);var i=this.column;this.order=this.count++%2;if(!e[config.sortMultiSortKey]){config.sortList=[];if(config.sortForce!=null){var a=config.sortForce;for(var j=0;j<a.length;j++){if(a[j][0]!=i){config.sortList.push(a[j]);}}}config.sortList.push([i,this.order]);}else{if(isValueInArray(i,config.sortList)){for(var j=0;j<config.sortList.length;j++){var s=config.sortList[j],o=config.headerList[s[0]];if(s[0]==i){o.count=s[1];o.count++;s[1]=o.count%2;}}}else{config.sortList.push([i,this.order]);}};setTimeout(function(){setHeadersCss($this[0],$headers,config.sortList,sortCSS);appendToTable($this[0],multisort($this[0],config.sortList,cache));},1);return false;}}).mousedown(function(){if(config.cancelSelection){this.onselectstart=function(){return false};return false;}});$this.bind("update",function(){this.config.parsers=buildParserCache(this,$headers);cache=buildCache(this);}).bind("sorton",function(e,list){$(this).trigger("sortStart");config.sortList=list;var sortList=config.sortList;updateHeaderSortCount(this,sortList);setHeadersCss(this,$headers,sortList,sortCSS);appendToTable(this,multisort(this,sortList,cache));}).bind("appendCache",function(){appendToTable(this,cache);}).bind("applyWidgetId",function(e,id){getWidgetById(id).format(this);}).bind("applyWidgets",function(){applyWidget(this);});if($.metadata&&($(this).metadata()&&$(this).metadata().sortlist)){config.sortList=$(this).metadata().sortlist;}if(config.sortList.length>0){$this.trigger("sorton",[config.sortList]);}applyWidget(this);});};this.addParser=function(parser){var l=parsers.length,a=true;for(var i=0;i<l;i++){if(parsers[i].id.toLowerCase()==parser.id.toLowerCase()){a=false;}}if(a){parsers.push(parser);};};this.addWidget=function(widget){widgets.push(widget);};this.formatFloat=function(s){var i=parseFloat(s);return(isNaN(i))?0:i;};this.formatInt=function(s){var i=parseInt(s);return(isNaN(i))?0:i;};this.isDigit=function(s,config){var DECIMAL='\\'+config.decimal;var exp='/(^[+]?0('+DECIMAL+'0+)?$)|(^([-+]?[1-9][0-9]*)$)|(^([-+]?((0?|[1-9][0-9]*)'+DECIMAL+'(0*[1-9][0-9]*)))$)|(^[-+]?[1-9]+[0-9]*'+DECIMAL+'0+$)/';return RegExp(exp).test($.trim(s));};this.clearTableBody=function(table){if($.browser.msie){function empty(){while(this.firstChild)this.removeChild(this.firstChild);}empty.apply(table.tBodies[0]);}else{table.tBodies[0].innerHTML="";}};}});$.fn.extend({tablesorter:$.tablesorter.construct});var ts=$.tablesorter;ts.addParser({id:"text",is:function(s){return true;},format:function(s){return $.trim(s.toLowerCase());},type:"text"});ts.addParser({id:"digit",is:function(s,table){var c=table.config;return $.tablesorter.isDigit(s,c);},format:function(s){return $.tablesorter.formatFloat(s);},type:"numeric"});ts.addParser({id:"currency",is:function(s){return/^[Â£$â‚¬?.]/.test(s);},format:function(s){return $.tablesorter.formatFloat(s.replace(new RegExp(/[^0-9.]/g),""));},type:"numeric"});ts.addParser({id:"ipAddress",is:function(s){return/^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);},format:function(s){var a=s.split("."),r="",l=a.length;for(var i=0;i<l;i++){var item=a[i];if(item.length==2){r+="0"+item;}else{r+=item;}}return $.tablesorter.formatFloat(r);},type:"numeric"});ts.addParser({id:"url",is:function(s){return/^(https?|ftp|file):\/\/$/.test(s);},format:function(s){return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//),''));},type:"text"});ts.addParser({id:"isoDate",is:function(s){return/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);},format:function(s){return $.tablesorter.formatFloat((s!="")?new Date(s.replace(new RegExp(/-/g),"/")).getTime():"0");},type:"numeric"});ts.addParser({id:"percent",is:function(s){return/\%$/.test($.trim(s));},format:function(s){return $.tablesorter.formatFloat(s.replace(new RegExp(/%/g),""));},type:"numeric"});ts.addParser({id:"usLongDate",is:function(s){return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));},format:function(s){return $.tablesorter.formatFloat(new Date(s).getTime());},type:"numeric"});ts.addParser({id:"shortDate",is:function(s){return/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);},format:function(s,table){var c=table.config;s=s.replace(/\-/g,"/");if(c.dateFormat=="us"){s=s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/,"$3/$1/$2");}else if(c.dateFormat=="uk"){s=s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/,"$3/$2/$1");}else if(c.dateFormat=="dd/mm/yy"||c.dateFormat=="dd-mm-yy"){s=s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/,"$1/$2/$3");}return $.tablesorter.formatFloat(new Date(s).getTime());},type:"numeric"});ts.addParser({id:"time",is:function(s){return/^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);},format:function(s){return $.tablesorter.formatFloat(new Date("2000/01/01 "+s).getTime());},type:"numeric"});ts.addParser({id:"metadata",is:function(s){return false;},format:function(s,table,cell){var c=table.config,p=(!c.parserMetadataName)?'sortValue':c.parserMetadataName;return $(cell).metadata()[p];},type:"numeric"});ts.addWidget({id:"zebra",format:function(table){if(table.config.debug){var time=new Date();}$("tr:visible",table.tBodies[0]).filter(':even').removeClass(table.config.widgetZebra.css[1]).addClass(table.config.widgetZebra.css[0]).end().filter(':odd').removeClass(table.config.widgetZebra.css[0]).addClass(table.config.widgetZebra.css[1]);if(table.config.debug){$.tablesorter.benchmark("Applying Zebra widget",time);}}});})(jQuery);

function ecordiaObject(ecordia_dependency, ecordia_element_title, ecordia_element_description){
    this.ecordiaDependency = ecordia_dependency;
    this.userDefinedTitle = ecordia_element_title;
    this.userDefinedDescription = ecordia_element_description;
    this.userDefined = 'user-defined';
    this.buttonClicked = false;
    this.errorThickbox = false;
    this.TB_WIDTH = 950;
    this.TB_HEIGHT = 600;
    this.original_TB_WIDTH = this.TB_WIDTH;
    this.original_TB_HEIGHT = this.TB_HEIGHT;
    this.elementMap = {
        'aioseo': {
            title: 'aiosp_title',
            description: 'aiosp_description'
        },
        'fvaioseo': {
            title: 'fvseo_title',
            description: 'fvseo_description'
        },
		'thesis': {
            title: 'thesis_title',
            description: 'thesis_description'
        },
        'hybrid': {
            title: 'Title',
            description: 'Description'
        },
		'headwa': {
            title: 'seo_title',
            description: 'seo_description'
        },
        'genesis': {
        	title: 'genesis_title',
        	description: 'genesis_description'
        },
        'woothemes': {
        	title: 'woothemes_seo_title',
        	description: 'woothemes_seo_description'
        }
    };
    
    this.showError = function(message, extended){
        ecordia.errorThickbox = true;
        this.TB_HEIGHT = 300;
        this.TB_WIDTH = 300;
        tb_show('Scribe Content Optimizer', 'media-upload.php?tab=ecordia-error&type=ecordia-error&message=' + encodeURIComponent(message) + '&extended=' + encodeURIComponent(extended) + '&TB_iframe=true', false);
    };
    
    this.checkElementComplete = function(val, elementId){
    	if('' != elementId) {
    		var $element = jQuery('#' + elementId).size() > 0 ? jQuery('#' + elementId) : jQuery('[name='+elementId+']');
	        if ('' == val) {
	            $element.addClass('incomplete').removeClass('complete');
	            return false;
	        }
	        else {
	            $element.addClass('complete').removeClass('incomplete');
	            return true;
	        }
    	}
    };
    
    this.getElementValue = function(element){
        if (element.attr('id') == 'content') {
            if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
                var value = ed.getContent().replace('<br />','');
				return value;
            }
            else {
                return jQuery.trim(element.val());
            }
        }
        else {
            return jQuery.trim(element.val());
        }
    }
    
    this.completeListItems = function(){
        var ecordia = this;
        jQuery.each(this.elementIds, function(name, value){
        	if('' != value) {
        		var $element = jQuery('#' + value).size() > 0 ? jQuery('#' + value) : jQuery('[name='+value+']');
        		ecordia.checkElementComplete(ecordia.getElementValue($element), 'ecordia-seo-analysis-requirement-' + name);
        	}
        });
    }
    
    this.toggleAjaxIndicator = function(){
        jQuery('#ecordia-ajax-feedback').toggleClass('ajax-feedback').toggle();
    }
    
    this.analyzing = false;
    
    this.analyzePost = function(event){
        event.preventDefault();
        if (ecordia.analyzeButtonEnabled() && !ecordia.analyzing) {
            ecordia.analyzing = true;
            ecordia.toggleAjaxIndicator();
            var post_ID = jQuery('#post_ID').val();
            if ('' == post_ID || post_ID < 1) {
                autosave();
            }
            else {
                ecordia.sendAnalysisRequest();
            }
            
        }
        else 
            if (!ecordia.analyzing) {
				ecordia.showError('Review Required','Missing Content, Title, or Description.');
            }
    };
    
    this.sendAnalysisRequest = function(){
    	if(ecordia.elementIds['title'] != '') {
    		var $titleElement = jQuery('#' + ecordia.elementIds['title']).size() > 0 ? jQuery('#' + ecordia.elementIds['title']) : jQuery('[name='+ecordia.elementIds['title']+']');
    		var $descriptionElement = jQuery('#' + ecordia.elementIds['title']).size() > 0 ? jQuery('#' + ecordia.elementIds['description']) : jQuery('[name='+ecordia.elementIds['description']+']');
	        jQuery.post('admin-ajax.php', {
	            'action': 'ecordia_analyze',
	            'title': ecordia.getElementValue($titleElement),
	            'content': ecordia.getElementValue(jQuery('#' + ecordia.elementIds['content'])),
	            'description': ecordia.getElementValue($descriptionElement),
	            'pid': jQuery('#post_ID').val()
	        }, function(data){
	            ecordia.analyzing = false;
	            ecordia.toggleAjaxIndicator();
	            if (data.success) {
	                jQuery('#ecordia .inside').html(data.meta);
	                jQuery('#ecordia-link-building .inside').html(data.linkMeta);
	                ecordia.registerHandlers();
	                jQuery('#ecordia-seo-analysis-review-button').click();
	            }
	            else {
	                ecordia.showError(data.message, data.extended);
	            }
	        }, 'json');
    	}
    }
    
    this.showReview = function(event){
        event.preventDefault();
        if (typeof(tb_show) != 'undefined') {
            $this = jQuery(this);
            tb_show('Scribe Content Analysis', $this.attr('href'), false);
        }
    };
    
    this.enabled = function(){
        return typeof(this.ecordiaDependency) != 'undefined' && this.ecordiaDependency != '' && (this.ecordiaDependency == this.userDefined || typeof(this.elementMap[this.ecordiaDependency]) != 'undefined');
    }
    
    this.analyzeButtonEnabled = function(){
        var shouldEnable = true;
        jQuery.each(this.elementIds, function(name, value){
        	if('' != value) {
    			var $element = jQuery('#' + value).size() > 0 ? jQuery('#' + value) : jQuery('[name='+value+']');
	            if (ecordia.getElementValue($element) == '') {
	                shouldEnable = false;
	            }
        	} else {
        		shouldEnable = false;
        	}
        	return shouldEnable;
        });
        return shouldEnable;
    }
    
    this.elementIds = {
        'title': '',
        'content': 'content',
        'description': ''
    }
    
    if (this.enabled()) {
    	if(this.ecordiaDependency == this.userDefined) { 
    		this.elementIds['title'] = this.userDefinedTitle;
    		this.elementIds['description'] = this.userDefinedDescription;
    	} else {
	        this.elementIds['title'] = this.elementMap[this.ecordiaDependency]['title'];
	        this.elementIds['description'] = this.elementMap[this.ecordiaDependency]['description'];
    	}
    }
    
    this.registerHandlers = function(){
        var ecordia = this;
        
        if (jQuery('#aiosp').length > 0) {
            jQuery('input[name=' + this.elementMap['aioseo']['title'] + ']').attr('id', this.elementMap['aioseo']['title']);
            jQuery('textarea[name=' + this.elementMap['aioseo']['description'] + ']').attr('id', this.elementMap['aioseo']['description']);
        }

        if (jQuery('#fvsimplerseopack').length > 0) {
            jQuery('input[name=' + this.elementMap['fvaioseo']['title'] + ']').attr('id', this.elementMap['fvaioseo']['title']);
            jQuery('textarea[name=' + this.elementMap['fvaioseo']['description'] + ']').attr('id', this.elementMap['fvaioseo']['description']);
        }
        
        jQuery('.ecordia-close-thickbox').click(function(event){
            event.preventDefault();
            top.tb_remove();
        });
        jQuery('#ecordia-setttings-page-from-thickbox').click(function(event){
            top.tb_remove();
        });
        jQuery('#ecordia-seo-analysis-analyze-button,#ecordia-seo-analysis-review-button').click(this.registerButtonClicked);
        jQuery('#ecordia-seo-analysis-analyze-button').click(this.analyzePost);
        jQuery('#ecordia-seo-analysis-review-button').click(this.showReview);
        jQuery.each(this.elementIds, function(name, id){
        	if(id!='') {
        		var $element = jQuery('#' + id).size() > 0 ? jQuery('#' + id) : jQuery('[name='+id+']');
	            $element.blur(function(event){
	                ecordia.blurEvent();
	            }).blur();
        	}
        });
        
        jQuery('#scribe-keyword-research-analyze-button').click(this.doKeywordResearch);
        jQuery('#scribe-keyword-research-phrase').keypress(function(event) {
        	if(event.which == 13) {
        		ecordia.doKeywordResearch(event);
        	}
        });
        
        jQuery('#scribe-link-building-get-topics').click(this.getLinkResearchTopics);
        jQuery('#scribe-link-building-add-keyword').click(this.appendToLinkResearchTerms);
        jQuery('.scribe-link-building-remove-keyword').live('click',this.removeFromLinkResearchTerms);
    };
    
    this.getLinkResearchTopics = function(event) {
    	event.preventDefault();
    	var $terms = jQuery('#scribe-link-building-keyword-terms-selected li span');
    	var termsString = jQuery.map($terms, function(n,i) { if('' == jQuery(n).text()) { return null; } else { return jQuery(n).text(); } }).join(',');
    	if(termsString == '') {
    		alert('Please select at least one keyword before retrieving related links.');
    		return;
    	}
    	jQuery('#scribe-link-building-get-topics').attr('disabled','disabled');
    	jQuery('#scribe-link-building-ajax-indicator').css('visibility','visible');
    	jQuery.post(
    		'admin-ajax.php',
    		{
    			action: 'scribe_link_building_research',
    			id: jQuery('#link-building-post-id').attr('data-id'),
    			type: jQuery('#link-building-type').attr('data-type'),
    			terms: termsString
    		},
    		function(data,status) {
    	    	jQuery('#scribe-link-building-get-topics').removeAttr('disabled');
    			jQuery('#scribe-link-building-ajax-indicator').css('visibility','hidden');
    			jQuery('#link-building-research-results-container').html(data);
    		},
    		'html'
		);
    };
    
    this.appendToLinkResearchTerms = function(event) {
    	event.preventDefault();
    	
    	var $selected = jQuery('#scribe-link-building-keyword-term option:selected');
    	var $clone = jQuery('#scribe-link-building-keyword-terms-selected-template').clone().removeAttr('id');
    	$clone.find('span').text($selected.val());
    	$clone.appendTo(jQuery('#scribe-link-building-keyword-terms-selected'));
    	$selected.remove();
    	
    	ecordia.toggleLinkResearchTermAddButton();
    };
    
    this.removeFromLinkResearchTerms = function(event) {
    	event.preventDefault();
    	
    	var $parent = jQuery(this).parent();
    	var $span = $parent.find('span');
    	var $clone = jQuery('<option></option>');
    	$clone.val($span.text()).text($span.text());
    	$clone.appendTo(jQuery('#scribe-link-building-keyword-term'));
    	$parent.remove();
    	
    	ecordia.toggleLinkResearchTermAddButton();
    };
    
    this.toggleLinkResearchTermAddButton = function() {
    	if(jQuery('#scribe-link-building-keyword-term option').size() == 0) {
    		jQuery('#scribe-link-building-keyword-term-container').hide();
    	} else {
    		jQuery('#scribe-link-building-keyword-term-container').show();
    	}
    }
    	
    this.doKeywordResearch = function(event) {
    	event.preventDefault();
    	var phrase = jQuery('#scribe-keyword-research-phrase').val();
    	var match = jQuery('#scribe-keyword-research-type').val();
    	jQuery('#scribe-keyword-research-ajax-feedback').css('visibility','visible');
    	jQuery('#scribe-keyword-research-analyze-button').attr('disabled','disabled');
    	
    	jQuery.post(
    		'admin-ajax.php',
    		{
    			action: 'scribe_keyword_research',
    			phrase: phrase,
    			match: match,
    			number: jQuery('#scribe-keyword-research-evaluations-left-number').text()
    		},
    		function(data,status) {
    			jQuery('#scribe-keyword-research-analyze-button').removeAttr('disabled');
    			jQuery('#scribe-keyword-research-ajax-feedback').css('visibility','hidden');
    			
    			if(data.error == 1) {
    				ecordia.showError("Could not find any keyword research for your terms.", "");
    			} else {
    				jQuery('#scribe-keyword-research-evaluations-left-number').text(data.number);
    				tb_show('Scribe Keyword Research', 'media-upload.php?tab=ecordia-keyword-research&type=ecordia-keyword-research&phrase='+encodeURIComponent(data.phrase)+'&match-type='+encodeURIComponent(data.match)+'&TB_iframe=true', false);
    			}
    		},
    		'json'
		);
    }
    
    this.blurEvent = function(){
        ecordia.completeListItems();
        if (ecordia.analyzeButtonEnabled()) {
            jQuery('#ecordia-seo-analysis-analyze-button').removeClass('ecordia-disabled');
        }
        else {
            jQuery('#ecordia-seo-analysis-analyze-button').addClass('ecordia-disabled');
        }
    }
    
    this.registerButtonClicked = function(event){
        ecordia.buttonClicked = true;
    }
}

jQuery(document).ready(function(){
	jQuery('#ecordia-connection-method').change(function() {
		if(jQuery(this).val() == 'https') {
			jQuery('#ecordia-https-warning').css({display:'block'});
		} else {
			jQuery('#ecordia-https-warning').css({display:'none'});
		}
	}).change();
	
	jQuery('input[name=ecordia-seo-tool-method]').change(function() {
		var $this = jQuery(this);
		if($this.is(':checked')) {
			if($this.val()=='') {
				jQuery('#ecordia-seo-tool-chooser-container').hide();
			} else {
				jQuery('#ecordia-seo-tool-chooser-container').show();
			}
		}
	}).change();
	
	jQuery('.alternate-keywords-table').tablesorter({
		textExtraction: 'complex'
	});
	
	jQuery('a.scribe-content-keyword').click(function(event) {
		event.preventDefault();
		
		var $this = jQuery(this);
		var $previous = jQuery('#scribe-content-keywords a.active');
		if($this.hasClass('active')) {
			return;
		}
		
		$previous.removeClass('active');
		$this.addClass('active');

		$toRemove = jQuery('#scribe-alternate-keywords .alternate-keywords-table'); 
		$toRemove.remove();
		$toRemove.addClass($previous.attr('id'));
		jQuery('#previously-fetched-alternates').append($toRemove);
		
		var $existing = jQuery('#previously-fetched-alternates .'+$this.attr('id'));
		if($existing.size() > 0) {
			jQuery('#scribe-alternate-keywords').append($existing);
		} else {
			jQuery('#fetching-alternate-keywords-message').show();
			jQuery.post(
				'admin-ajax.php',
				{
					action: 'ecordia_keyword_alternates',
					seed: $this.text()
				},
				function(data,status) {
					jQuery('#fetching-alternate-keywords-message').hide();
					jQuery('#scribe-alternate-keywords').append(data);
					jQuery('.alternate-keywords-table').tablesorter({
						textExtraction: 'complex'
					});
				},
				'html'
			);
		}
	});
	
    if (typeof(ecordia) == 'object') {
        ecordia.registerHandlers();
        if (ecordia.enabled()) {
            if (typeof(tb_position) != 'undefined') {
                // CRAZY ThickBox positioning stuff so that the Thickbox isn't overridden by WordPress
                var ecordia_old_tb_position = tb_position;
                tb_position = function(){
                    if (ecordia.buttonClicked) {
                        var tbWindow = jQuery('#TB_window');
                        var W = ecordia.TB_WIDTH;
                        var H = ecordia.TB_HEIGHT;
                        
                        var fromTop = ((jQuery(window).height() - H) / 2);
                        if (tbWindow.size()) {
                            tbWindow.width(W - 50).height(H - 45);
                            tbWindow.css('marginTop', fromTop);
                            jQuery('#TB_iframeContent').width(W - 50).height(H - 75);
                            tbWindow.css({
                                'margin-left': '-' + parseInt(((W - 50) / 2), 10) + 'px'
                            });
                            if (typeof document.body.style.maxWidth != 'undefined') {
                                tbWindow.css({
                                    'top': '10px',
                                    'margin-top': fromTop
                                });
                            }
                            jQuery('#TB_title').css({
                                'background-color': '#222',
                                'color': '#cfcfcf'
                            });
                        };
                                            }
                    else {
                        ecordia_old_tb_position();
                    }
                };
                var ecordia_old_tb_remove = tb_remove;
                tb_remove = function(){
                    if (ecordia.buttonClicked) {
                        ecordia.buttonClicked = false;
                    }
                    if (ecordia.errorThickbox) {
                        ecordia.errorThickbox = false;
                        ecordia.TB_HEIGHT = ecordia.original_TB_HEIGHT;
                        ecordia.TB_WIDTH = ecordia.original_TB_WIDTH;
                    }
                    ecordia_old_tb_remove();
                };
            }
            if (typeof(autosave_saved_new) != 'undefined') {
                var ecordia_old_autosave_saved_new = autosave_saved_new;
                autosave_saved_new = function(response){
                    ecordia_old_autosave_saved_new(response);
                    if (ecordia.analyzing) {
                        ecordia.sendAnalysisRequest();
                    }
                }
            }
        }
    }
});
