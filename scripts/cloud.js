/* 
<div class="d3-word-cloud-container">

	<input type="hidden" class="font-family" value="Helvetica">
	<input type="hidden" class="font-size" value="20,120">
	<input type="hidden" class="font-color" value="black">
	<input type="hidden" class="tags" value="{&quot;name&quot;:&quot;quest&quot;,&quot;count&quot;:3,&quot;url&quot;:&quot;http:\/\/localhost\/wordpress-ms\/news-site\/tag\/quest\/&quot;}">
	<canvas width="800" height="400"></canvas>

</div>
*/


function d3_word_cloud( div )
{
	var self = {}
	
	self.id = '#'+div.id+'.d3-word-cloud-container';
	self.words;
	self.used_words;
	self.word_count;
	self.tag_count;
	
	self.draw = function( data, bounds )
	{
		self.word_count = data.length;
		self.used_words = [];
		for( var i = 0; i < data.length; i++ )
		{
			self.used_words.push( data[i].text );
		}
		
		var anchors = self.vis
			.selectAll( 'a' )
				.data( data )
				.enter()
				.append( 'a' )
					.attr( 'xlink:href', function(d,i) { return self.words[d.text].url; } );
					//.attr( 'target', '_blank' )
		
		anchors
			.append( 'title' )
				.text( function(d) { return self.words[d.text].count+' matches'; } );
		
		anchors
			.append( 'text' )
				.attr( 'text-anchor', 'middle' )
				.attr( 'transform', function(d) { return 'translate(' + [d.x, d.y] + ')rotate(' + d.rotate + ')'; } )
				.style( 'font-size', function(d) { return d.size + 'px'; } )
				.style( 'font-family', function(d) { return d.font; } )
				.style( 'fill', function(d) { return self.fill( self.words[d.text].count ); } )
				//.attr( 'class', function(d) { return quantize( words[d.text].count ); } )
				.text( function(d) { return d.text; } );
		
		var s = "";
		for( var word in self.words )
		{
			if( self.used_words.indexOf(word) == -1 )
			{
				s += word+" ["+self.words[word].count+"]<br/>";
			}
		}
		d3.select( self.id )
			.append( 'div' )
				.attr( 'class', 'debug-data' )
				.attr( 'style', 'text-align:left' )
				.html( self.word_count+' of '+self.tag_count+' were placed.  Words not placed:<br/>'+s );
	}
	
	self.process_cloud = function()
	{
		var font_family = unescape( d3.select(self.id+' .font-family').attr('value') );

		var font_size = unescape( d3.select(self.id+' .font-size').attr('value') );
		font_size = font_size.split(',');
		if( font_size.length < 2 )
		{
			if( font_size.length == 1 ) font_size = [ font_size[0], font_size[0] ];
			else font_size = [ 10, 100 ];
		}
		font_size = d3.scale['log']().range( font_size );

		var font_color = unescape( d3.select(self.id+' .font-color').attr('value') );
		font_color = font_color.split(',');
	
		var tags = unescape( d3.select(self.id+' .tags').attr('value') );
		tags = JSON.parse( tags );

		var canvas = d3.select(self.id+' svg');
		canvas_size = [ +canvas.attr('width'), +canvas.attr('height') ];

		var layout = d3.layout.cloud()
			.timeInterval( 10 )
			.rotate( 0 )
			//.rotate( function() { return Math.round(Math.random()) * 90; } )
			//.rotate( function() { return Math.round(Math.random() * 4) * 45; } )
			.spiral( 'rectangular' )
			.size( canvas_size )
			.font( font_family )
			.fontSize( function(d) { return font_size(+d.count); } )
			.text( function(d) { return d.name; } )
			.on( 'end', self.draw );

		var background = canvas.append( 'g' );
		var vis = canvas.append( 'g' )
			.attr( 'transform', 'translate(' + [canvas_size[0] >> 1, canvas_size[1] >> 1] + ')' );

		if( tags.length )
		{
			var min = d3.min( tags, function(d) { return +d.count; } );
			var max = d3.max( tags, function(d) { return +d.count; } );
			var quantize = d3.scale.quantize()
				.domain( [0, max] )
				.range( d3.range(20).map(function(i) { return 'd3-word-cloud-text-'+i; }) );

			if( font_color.length == 1 ) font_color = [ font_color[0], font_color[1] ];
			var increment = max / font_color.length+1;
			var fill_domain = [];
			for( var i = 0; i < font_color.length; i++ ) { fill_domain.push(increment*i); }
			if( fill_domain.length == 1 ) fill_domain = [ fill_domain[0], max ];

			var fill = d3.scale.linear().domain(fill_domain).range(font_color);
			font_size.domain( [min, max] );
		}
		else
		{
			// no tags...
			return;
		}

		layout.stop().words( tags );

		var words = {};
		for( var i = 0; i < tags.length; i++ )
		{
			words[ tags[i].name ] = tags[i];
		}
	
		self.tag_count = tags.length;
		self.words = words;
		self.vis = vis;
		self.fill = fill;
	
		layout.start();
	}

	self.process_cloud();
}

window.onload = function()
{
	var divs = d3.selectAll('.d3-word-cloud-container');
	var clouds = [];
	
	for( var i = 0; i < divs[0].length; i++ )
	{
		var cloud = new d3_word_cloud(divs[0][i]);
		//cloud.process_cloud();
		clouds.push(cloud);
	}
}



