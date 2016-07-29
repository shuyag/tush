<?php header('Content-Type: text/html; charset=utf-8'); ?>
<html>
	<head>
		<meta charset=utf-8>
		<title>Seating Visualization</title>
		<link href="inner/css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="inner/css/style.css" rel="stylesheet">
		<style>
			body { margin: 0; }
			canvas { width: 100%; height: 100%; position: absolute; z-index: -1;}
			.header {
				background-color: #f2f2f2;  
				color: black;
				position: absolute;
				z-index: 9999;
				width: 100%;
				height: 50px;
			}
			.header-img {
				height: 40px;
				position: absolute;
				z-index: 9999;
				padding: 5px;

			}
			#ThreeJS {
				position: relative;
			}
			.selected {
				font-weight: bold;
			}
			.navbar-nav li {
				background-color: #f2f2f2;
			}
		</style>
	</head>
	<body>
		
		<div id="ThreeJS">
			<div class="header" id="header">
				<!-- old stuff <img src="img/header.png" class="header-img"> --> 
				 <div class="container">
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="index.html">
                    <h1><img src="img/header.png" class="header-img"></h1>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
      <ul class="nav navbar-nav">
        <li><a href="inner/insights.html">How It Works</a></li>
		<li><a href="inner/applications.html">Applications</a></li>
		<li class="selected"><a href="#">Dashboard</a></li>
      </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>

			</div>
		</div>

	<script src="js/Three.js"></script>
	<script src="js/Detector.js"></script>
	<script src="js/Stats.js"></script>
	<script src="js/OrbitControls.js"></script>
	<script src="js/THREEx.KeyboardState.js"></script>
	<script src="js/THREEx.FullScreen.js"></script>
	<script src="js/THREEx.WindowResize.js"></script>
	<script type="text/javascript" src="//cdn.jsdelivr.net/particle-api-js/5/particle.min.js">
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>

<?php

	$deviceID_0 = "fakeID";
	$access_token_0 = "fakeToken";

	$deviceID_1 = "fakeID";
	$access_token_1 = "fakeToken";
	
	$url_0 = "https://api.particle.io/v1/devices/$deviceID_0/";
	$formed_url_0 ='?access_token='.$access_token_0;
	$variable_name_0 = "publicState";

	$url_1 = "https://api.particle.io/v1/devices/$deviceID_1/";
	$formed_url_1 ='?access_token='.$access_token_1;
	$variable_name_1 = "publicState";

?>
		<!--<script src="three.js"></script>-->
		<script>

		/* CONSTANTS */ 
		const SEATSIZE = 50;
		const SEATFLOAT = 20;
		const TABLE_HEIGHT = (SEATSIZE * 1.4);
		const TABLE_LENGTH = (SEATSIZE * 4.5);
		const TABLE_WIDTH = (SEATSIZE * 1.5); 
		const SPOTLIGHT_HEIGHT = 1950;
		const LIGHT_HEIGHT = 350; 
		const LIGHT_COLOR = 0x404040;
		const LIGHT_FACTOR = 0.7;
		// colors
		const SEAT_COLOR = 0x08F1C4;
		const SEAT_OFF_COLOR = 0x8E3BA5;
		const TABLE_COLOR = 0x404241;
		var seatCounter = 0;
		var cube;
		var sitSound = new sound("sitSound.mp3");
		var standSound = new sound("standSound.mp3");
		
		/*
	Three.js "tutorials by example"
	Author: Lee Stemkoski
	Date: July 2013 (three.js v59dev)
 */
	
	//////////	
	// MAIN //
	//////////
	// standard global variables
	var container, scene, camera, renderer, controls, stats;
	var keyboard = new THREEx.KeyboardState();
	var clock = new THREE.Clock();

	// custom global variables
		// build a class for seats rather than trying to build new things
	var Seat = class Seat {
		// wrapper for name
		constructor(xpos,ypos,zpos) {
			//var seatGeometry = new THREE.CubeGeometry(SEATSIZE, SEATSIZE, SEATSIZE, 1, 1, 1 );
			var seatGeometry = new THREE.CylinderGeometry(SEATSIZE/2, SEATSIZE/2, SEATSIZE, 80 );
			// var modifier = new THREE.SubdivisionModifier( 2 );
			// modifier.modify(seatGeometry);
			var seatMaterial = getSeatMaterial();
			//this.name = "seat"+seatCounter;
			//var namestring = "seat"+seatCounter; 
			this.seat = new THREE.Mesh(seatGeometry, seatMaterial);
			this.seat.castShadow = true;
			this.seat.position.set(xpos, ((SEATSIZE / 2) + SEATFLOAT), zpos);
			scene.add(this.seat);
			this.status = 0;
			seatCounter++;
		}

		getStatus() {
			return this.status;
		}

		setStatus(newStatus) {
			this.status = newStatus;
		}

		change() {
			// someone takes the seat
			if (this.status == 0) {
				this.seat.material = new THREE.MeshPhongMaterial( { color: SEAT_OFF_COLOR, transparent: true, opacity: 0.65 });
				this.seat.position.y = this.seat.position.y - SEATFLOAT;
				this.status = 1;
				sitSound.play();
			}
			// seat is available again
			else 
			{
				this.seat.material = new THREE.MeshPhongMaterial( { color: SEAT_COLOR, transparent: false, opacity: 0.8 });
				this.seat.position.y = this.seat.position.y + SEATFLOAT;
				this.status = 0;
				standSound.play();
			}
			
		}
	}
	var Table = class Table {
		// wrapper for name
		constructor(xpos,zpos) {
			var tableGeometry = new THREE.CubeGeometry(TABLE_LENGTH, TABLE_HEIGHT, TABLE_WIDTH, 1, 1, 1 );
			var tableMaterial = getTableMaterial();
			//this.name = "seat"+seatCounter;
			//var namestring = "seat"+seatCounter; 
			this.table = new THREE.Mesh(tableGeometry, tableMaterial);
			this.table.castShadow = true;
			this.table.position.set(xpos, ((TABLE_HEIGHT / 2)), zpos);
			scene.add(this.table);
			this.status = 0;
		}
	}
	var Spotlight = class Spotlight {
		constructor(xpos,zpos) {
			var spotlight = new THREE.SpotLight(0x909090);
			spotlight.position.set(xpos,SPOTLIGHT_HEIGHT,zpos);
			spotlight.angle = (Math.PI/2);
			//spotlight.shadowCameraVisible = true;
			spotlight.shadowDarkness = 0.25;
			spotlight.intensity = 0.5;
			//spotlight.target = (xpos,0,zpos);
			// must enable shadow casting ability for the light
			spotlight.castShadow = true;
			//scene.add(spotlight.target);
			scene.add(spotlight);
		}
	}
	var Line = class Line {
		constructor(x1,y1,z1,x2,y2,z2) {
			var material = new THREE.LineBasicMaterial({
        color: 0x000000
	    });
	    var geometry = new THREE.Geometry();
	    geometry.vertices.push(new THREE.Vector3(x1,y1,z1));
	    geometry.vertices.push(new THREE.Vector3(x2,y2,z2));
	    var line = new THREE.Line(geometry, material);
	    scene.add(line);
		}
	}

	// initialization
	init();
	// animation loop / game loop
	animate();
	///////////////
	// FUNCTIONS //
	///////////////
				
	function init() 
	{
		///////////
		// SCENE //
		///////////
		scene = new THREE.Scene();
		////////////
		// CAMERA //
		////////////
		
		// set the view size in pixels (custom or according to window size)
		// var SCREEN_WIDTH = 400, SCREEN_HEIGHT = 300;
		var SCREEN_WIDTH = window.innerWidth, SCREEN_HEIGHT = window.innerHeight - 50;	
		// camera attributes
		var VIEW_ANGLE = 45, ASPECT = SCREEN_WIDTH / SCREEN_HEIGHT, NEAR = 0.1, FAR = 20000;
		// set up camera
		camera = new THREE.PerspectiveCamera( VIEW_ANGLE, ASPECT, NEAR, FAR);
		// add the camera to the scene
		scene.add(camera);
		// the camera defaults to position (0,0,0)
		// 	so pull it back (z = 400) and up (y = 100) and set the angle towards the scene origin
		camera.position.set(350,450,-550);
		camera.lookAt(scene.position);	
		
		//////////////
		// RENDERER //
		//////////////
		
		// create and start the renderer; choose antialias setting.
		if ( Detector.webgl )
			renderer = new THREE.WebGLRenderer( {antialias:true} );
		else
			renderer = new THREE.CanvasRenderer(); 
		
		renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);

		renderer.shadowMapEnabled = true;
		
		// attach div element to variable to contain the renderer
		container = document.getElementById( 'header' );
		// alternatively: to create the div at runtime, use:
		//   container = document.createElement( 'div' );
		//    document.body.appendChild( container );
		
		// attach renderer to the container div
		container.appendChild( renderer.domElement );
		
		////////////
		// EVENTS //
		////////////
		// automatically resize renderer
		THREEx.WindowResize(renderer, camera);
		// toggle full-screen on given key press
		THREEx.FullScreen.bindKey({ charCode : 'm'.charCodeAt(0) });
		
		//////////////
		// CONTROLS //
		//////////////
		// move mouse and: left   click to rotate, 
		//                 middle click to zoom, 
		//                 right  click to pan
		controls = new THREE.OrbitControls( camera, renderer.domElement );
		
		///////////
		// LIGHT //
		///////////
		
		// create a light

		// TODO: FACTOR OUT to Light class/function IF TIME
		

		var light = new THREE.PointLight(LIGHT_COLOR);
		light.position.set(0,LIGHT_HEIGHT,0);
		scene.add(light);
		light.castShadow = true;

		var ambientLight = new THREE.AmbientLight(0x101010);
		scene.add(ambientLight);
		
		var light2 = new THREE.PointLight(LIGHT_COLOR);
		light.position.set(TABLE_LENGTH*2,LIGHT_HEIGHT,-TABLE_LENGTH*2);
		scene.add(light2);
		light2.castShadow = true;

		var light3 = new THREE.PointLight(LIGHT_COLOR);
		light3.position.set(-TABLE_LENGTH*2,LIGHT_HEIGHT,-TABLE_LENGTH*2);
		scene.add(light3);
		light3.castShadow = true;

		var light4 = new THREE.PointLight(LIGHT_COLOR);
		light4.position.set(-TABLE_LENGTH*2,LIGHT_HEIGHT,TABLE_LENGTH*2);
		scene.add(light4);
		light4.castShadow = true;

		var light5 = new THREE.PointLight(LIGHT_COLOR);
		light5.position.set(TABLE_LENGTH*2,LIGHT_HEIGHT,TABLE_LENGTH*2);
		scene.add(light5);
		light5.castShadow = true;

		light.shadowCameraVisible = true;
		light2.shadowCameraVisible = true;
		light3.shadowCameraVisible = true;
		light4.shadowCameraVisible = true;
		light5.shadowCameraVisible = true;

		
		spot1 = new Spotlight(TABLE_LENGTH*LIGHT_FACTOR, TABLE_LENGTH*LIGHT_FACTOR);
		spot2 = new Spotlight(TABLE_LENGTH*LIGHT_FACTOR, -TABLE_LENGTH*LIGHT_FACTOR);
		spot3 = new Spotlight(-TABLE_LENGTH*LIGHT_FACTOR, TABLE_LENGTH*LIGHT_FACTOR);
		spot4 = new Spotlight(-TABLE_LENGTH*LIGHT_FACTOR, -TABLE_LENGTH*LIGHT_FACTOR);

		
		//////////////
		// GEOMETRY //
		//////////////
			
		// most objects displayed are a "mesh":
		//  a collection of points ("geometry") and
		//  a set of surface parameters ("material")	
		// Sphere parameters: radius, segments along width, segments along height
		// var sphereGeometry = new THREE.SphereGeometry( 50, 32, 16 ); 
		// use a "lambert" material rather than "basic" for realistic lighting.
		//   (don't forget to add (at least one) light!)
		// var sphereMaterial = new THREE.MeshLambertMaterial( {color: 0x8888ff} ); 
		// var sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
		// sphere.position.set(100, 50, -50);
		// scene.add(sphere);
		
		// Create an array of materials to be used in a cube, one for each side
		
		// Cube parameters: width (x), height (y), depth (z), 
		//        (optional) segments along x, segments along y, segments along z
		var cubeGeometry = new THREE.CubeGeometry(50, 50, 50, 1, 1, 1 );
		// using THREE.MeshFaceMaterial() in the constructor below
		//   causes the mesh to use the materials stored in the geometry
		var blackCubeMaterials = new THREE.MeshFaceMaterial(solidColorArray(0x0f4094));
		//cube = new THREE.Mesh( cubeGeometry, cubeMaterials );
		cube = new THREE.Mesh(cubeGeometry, blackCubeMaterials);
		cube.position.set(-100, 35, -50);
		//scene.add( cube );	

		//var seatName = "Testing"; 
		//this[seatName] = new Seat(-100,150,100);
		
		var table1 = new Table(TABLE_LENGTH*-0.5,TABLE_LENGTH*0.5+(TABLE_WIDTH/2));
		var table2 = new Table(TABLE_LENGTH*0.5,TABLE_LENGTH*0.5+(TABLE_WIDTH/2));
		var table3 = new Table(TABLE_LENGTH-(TABLE_WIDTH/2), 0);
		table3.table.rotateY(Math.PI/2);
		var table4 = new Table(TABLE_LENGTH*0.5, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2));
		var table5 = new Table(TABLE_LENGTH*-0.5, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2));

		// SEATS - outside tables  
		// - table 1 - 
		makeNewSeat(TABLE_LENGTH*-0.5+SEATSIZE, TABLE_LENGTH*0.5+(TABLE_WIDTH/2)+((TABLE_WIDTH+SEATSIZE)/2)+SEATSIZE/3);
		makeNewSeat(TABLE_LENGTH*-0.5-SEATSIZE, TABLE_LENGTH*0.5+(TABLE_WIDTH/2)+((TABLE_WIDTH+SEATSIZE)/2)+SEATSIZE/3);
		// - table 2 - 
		makeNewSeat(TABLE_LENGTH*0.5-SEATSIZE, TABLE_LENGTH*0.5+(TABLE_WIDTH/2)+((TABLE_WIDTH+SEATSIZE)/2)+SEATSIZE/3)
		makeNewSeat(TABLE_LENGTH*0.5+SEATSIZE, TABLE_LENGTH*0.5+(TABLE_WIDTH/2)+((TABLE_WIDTH+SEATSIZE)/2)+SEATSIZE/3)
		// - table 3 - 
		makeNewSeat(TABLE_LENGTH + SEATSIZE/2 + SEATSIZE/3, TABLE_LENGTH*0.5-SEATSIZE);
		makeNewSeat(TABLE_LENGTH + SEATSIZE/2 + SEATSIZE/3, TABLE_LENGTH*-0.5+SEATSIZE);
		// - table 4 - 
		makeNewSeat(TABLE_LENGTH*0.5+SEATSIZE, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2)-((TABLE_WIDTH+SEATSIZE)/2)-SEATSIZE/3);
		makeNewSeat(TABLE_LENGTH*0.5-SEATSIZE, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2)-((TABLE_WIDTH+SEATSIZE)/2)-SEATSIZE/3);
		// - table 5 - 
		makeNewSeat(TABLE_LENGTH*-0.5+SEATSIZE, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2)-((TABLE_WIDTH+SEATSIZE)/2)-SEATSIZE/3);
		makeNewSeat(TABLE_LENGTH*-0.5-SEATSIZE, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2)-((TABLE_WIDTH+SEATSIZE)/2)-SEATSIZE/3);
		
		// SEATS - inside tables 
		// - table 1 - 
		makeNewSeat(TABLE_LENGTH*-0.5+SEATSIZE/2, TABLE_LENGTH*0.5+(TABLE_WIDTH/2)-((TABLE_WIDTH+SEATSIZE)/2)-SEATSIZE/3);
		// - table 2 - 
		makeNewSeat(TABLE_LENGTH*0.5-SEATSIZE/2, TABLE_LENGTH*0.5+(TABLE_WIDTH/2)-((TABLE_WIDTH+SEATSIZE)/2)-SEATSIZE/3);
		// - table 3 - 
		makeNewSeat(TABLE_LENGTH-(TABLE_WIDTH/2)-TABLE_WIDTH/2-SEATSIZE/2-SEATSIZE/3, 0);
		// - table 4 - 
		makeNewSeat(TABLE_LENGTH*0.5-SEATSIZE/2, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2)+((TABLE_WIDTH+SEATSIZE)/2)+SEATSIZE/3);
		// - table 5 - 
		makeNewSeat(TABLE_LENGTH*-0.5+SEATSIZE/2, TABLE_LENGTH*-0.5-(TABLE_WIDTH/2)+((TABLE_WIDTH+SEATSIZE)/2)+SEATSIZE/3);

		// Add TV
		var tvGeometry = new THREE.CubeGeometry(SEATSIZE/10, SEATSIZE*1.5, SEATSIZE*4, 1, 1, 1 );
		var tvMaterials = new THREE.MeshLambertMaterial({color: 0x000000});
		var tv = new THREE.Mesh(tvGeometry, tvMaterials);
		tv.position.set(-TABLE_LENGTH*1.5, SEATSIZE*2.5, 0);
		tv.castShadow = true;
		scene.add(tv);	


		// var darkTranslucentMaterial = new THREE.MeshPhongMaterial( { color: 0x333333, transparent: true, opacity: 0.75 } );
		// second = new THREE.Mesh(cubeGeometry, darkTranslucentMaterial);
		// second.position.set(-100, 25, -100);
		// scene.add( second );
		
		///////////
		// FLOOR //
		///////////
		
		// note: 4x4 checkboard pattern scaled so that each square is 25 by 25 pixels.
		var floorTexture = new THREE.ImageUtils.loadTexture( 'images/checkerboard.jpg' );
		floorTexture.wrapS = floorTexture.wrapT = THREE.RepeatWrapping; 
		floorTexture.repeat.set( 10, 10 );
		// DoubleSide: render texture on both sides of mesh
		// var floorMaterial = new THREE.MeshBasicMaterial( { map: floorTexture, side: THREE.DoubleSide } );
		var floorMaterial = new THREE.MeshLambertMaterial( { color: 0x5f5f5f, side: THREE.DoubleSide } );
		var floorGeometry = new THREE.PlaneGeometry(1000, 800, 1, 1);
		var floor = new THREE.Mesh(floorGeometry, floorMaterial);
		floor.position.y = -0.5;
		floor.rotation.x = Math.PI / 2;
		floor.receiveShadow = true;
		scene.add(floor);

		///////////
		// "WALLS" //
		///////////

		var wall1 = new Line(-500,0,400,-500,300,400);
		var wall2 = new Line(-500,0,-400,-500,300,-400);
		var wall3 = new Line(500,0,-400,500,300,-400);
		var wall4 = new Line(500,0,400,500,300,400);
		
		
		/////////
		// SKY //
		/////////
		
		// recommend either a skybox or fog effect (can't use both at the same time) 
		// without one of these, the scene's background color is determined by webpage background
		// make sure the camera's "far" value is large enough so that it will render the skyBox!
		var skyBoxGeometry = new THREE.CubeGeometry( 10000, 10000, 10000 );
		// BackSide: render faces from inside of the cube, instead of from outside (default).
		var skyBoxMaterial = new THREE.MeshBasicMaterial( { color: 0x9999ff, side: THREE.BackSide } );
		var skyBox = new THREE.Mesh( skyBoxGeometry, skyBoxMaterial );
		// scene.add(skyBox);
		
		// fog must be added to scene before first render
		scene.fog = new THREE.FogExp2( 0x9999ff, 0.00025 );
	}
	function animate() 
	{
	    requestAnimationFrame( animate );
			render();		
			update();
	}
	function update()
	{
		// delta = change in time since last call (in seconds)
		var delta = clock.getDelta(); 
		// functionality provided by THREEx.KeyboardState.js
		if ( keyboard.pressed("1") )
			document.getElementById('message').innerHTML = ' Have a nice day! - 1';	
		if ( keyboard.pressed("2") )
			document.getElementById('message').innerHTML = ' Have a nice day! - 2 ';	
			
		controls.update();
		//stats.update();
	}
	function render() 
	{	
		renderer.shadowMapEnables = true;
		renderer.shadowMapType = THREE.PCFSoftShadowMap;
		renderer.render( scene, camera );
	}
	function getSeatMaterial() {
		return (new THREE.MeshLambertMaterial( { color: SEAT_COLOR, transparent: true, opacity: 0.9 } ));
	}
	function getTableMaterial() {
		return (new THREE.MeshLambertMaterial( { color: TABLE_COLOR, transparent: true, opacity: 0.75 } ));
	}
	function solidColorArray(colorChoice)
	{
		var colorMaterialArray = [];
		for (var i = 0; i < 6; i++) {
			colorMaterialArray.push(new THREE.MeshLambertMaterial({ color: colorChoice }) );
		}
		return colorMaterialArray;
	}
	function makeNewSeat(xpos,zpos) {
		this[("seat"+seatCounter)] = new Seat(xpos,(SEATSIZE/2),zpos);
	}
	function sound(src) {
	    this.sound = document.createElement("audio");
	    this.sound.src = src;
	    this.sound.setAttribute("preload", "auto");
	    this.sound.setAttribute("controls", "none");
	    this.sound.style.display = "none";
	    document.body.appendChild(this.sound);
	    this.play = function(){
	        this.sound.play();
	    }
	    this.stop = function(){
	        this.sound.pause();
	    }
	}
	function randomChange() {
		var num = Math.floor((Math.random() * seatCounter)); 
		if (num == 0)
		{
			num = 1;
		}
		var namestring = "seat"+num;
		this[namestring].change();
		console.log("called rando!")
	}
	function changeAfterDelay (speed, min) {
		var delay = Math.floor((Math.random() * speed) + min); 
		setTimeout(randomChange, delay); 
		console.log("called here!")
	}
	function simulate(numIterationsToRun, speed, min) {
		for (var j = 0; j < numIterationsToRun; j++)
		{
			changeAfterDelay(speed, min);
		}
	}
	function setAll(bin){
		for (var k = 0; k < seatCounter; k++) {
			var namestring = "seat"+k;
			console.log(namestring);
			var stat = this[namestring].status;
			if (stat == bin) {
				this[namestring].change();
			}
		}
	}
	
	
		// order to add materials: x+,x-,y+,y-,z+,z-
		
			// CUBE MATERIALS FACES //
			// var cubeMaterialArray = [];
			// order to add materials: x+,x-,y+,y-,z+,z-
			// cubeMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0xff3333 } ) );
			// cubeMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0xff8800 } ) );
			// cubeMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0xffff33 } ) );
			// cubeMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0x33ff33 } ) );
			// cubeMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0x3333ff } ) );
			// cubeMaterialArray.push( new THREE.MeshBasicMaterial( { color: 0x8833ff } ) );
			// var cubeMaterials = new THREE.MeshFaceMaterial( cubeMaterialArray );


		</script>
			<script>
			var priorStatus_0 = 0;
			function checkSeat4() {
				console.log("checkSeat4 was called");
				var the_url = "<?=$url_0.$variable_name_0.$formed_url_0?>";

		    $.ajax({
		        url: the_url,
		        type:'get',
		        success: function(data){
		            //alert(data);
		            console.log(data);
		            console.log(data.result);
		            var sitStatus_0 = data.result;
		            if (sitStatus_0 != priorStatus_0) {
		            	seat4.change();
		            	priorStatus_0 = sitStatus_0;
		            	console.log("triggered!");
		            }
							}
						});
		  }
			//setInterval(checkSeat4,3000); 
	</script>
	<script>
			var priorStatus_1 = 0;
			function checkSeat7() {
				console.log("checkSeat7 was called");
				var the_url = "<?=$url_1.$variable_name_1.$formed_url_1?>";

		    $.ajax({
		        url: the_url,
		        type:'get',
		        success: function(data){
		            //alert(data);
		            console.log(data);
		            console.log(data.result);
		            var sitStatus_1 = data.result;
		            if (sitStatus_1 != priorStatus_1) {
		            	seat7.change();
		            	priorStatus_1 = sitStatus_1;
		            	console.log("triggered!");
		            }
							}
						});
		  }
			//setInterval(checkSeat7,5300); 
	</script>
	</body>
</html>