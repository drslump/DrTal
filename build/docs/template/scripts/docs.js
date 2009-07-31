window.addEvent('domready', function(){
	
	$$('.MGroupContent').setStyle('display', 'block');
	
	var selected = $('MSelected');
	
	var current = (selected) ? selected.getParent().getParent() : false;

	$$('div.MGroup').each(function(div){
		var link = div.getElement('a');
		var block = link.getNext();
		
		var fx = new Fx.Slide(block);
		
		if (block != current) fx.hide();

		link.addEvent('click', function(){
			fx.toggle();
		});
		
	});
	
	$$('.CTitle').each(function(heading){
		new Element('a', {
			'href': '#MainTopic',
			'class': 'toTop'
		}).setHTML('top').injectBefore(heading.getFirst());
	});
	
	new SmoothScroll();
	
	/* setup tooltips */
	new Tips( $$('a[title]'), {
		maxOpacity: 0.9, 
		maxTitleChars: 80
	});
	
	
	/*
	$$('div.CBody').each( function(el) {
		
		var h = el;
		while (h && h.className !== 'CTitle')
			h = h.previousSibling;
			
		if (h) {
			var slider = new Fx.Slide(el);
			slider.slideOut();
			h.addEvent( 'click', function(e) {
				e = new Event(e);
				slider.toggle();
				e.stop();
			});	
		}
	});
	*/
	
	/*
	$$('a.commentsMark').each( function(el) {
		var n = Math.round(Math.random()*10);
		if (!n) {
			el.setAttribute('title', 'No Comments yet');
			el.className += ' noComments';	
		} else {
			el.setAttribute('title', n + ' comments');
		}		
		
		el.appendChild( document.createTextNode( n + ' comments' ) );
		
		var name = el.id.replace(/^comment_(.*)$/, '$1');
		
		var slider = new Fx.Slide( document.getElementById('body_' + name) );
		el.addEvent( 'click', function(e) {
			e = new Event(e);
			slider.toggle();
			e.stop();
		});
	});
	*/
});

document.write(
	'<style type="text/css" media="screen">' +
	'div.MGroupContent{display: none} ' +
	/*'div.CTopic p.summary{display: none} ' + */
	'</style>'
);