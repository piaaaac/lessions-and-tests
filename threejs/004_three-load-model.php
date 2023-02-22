<!---------------------------------------------------------
--  Load a model
--  via https://threejs.org/docs/#examples/en/loaders/OBJLoader
----------------------------------------------------------->

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>threejs basics</title>
<link rel="stylesheet" href="https://unpkg.com/minimal-css-reset@1.1.0/reset.min.css">
<style>
  body {
    background: #666;
  }
</style>
</head>
<body>

<!-- <script src="https://cdn.jsdelivr.net/npm/three-obj-loader@1.1.3/dist/index.min.js"></script> -->

<script async src="https://unpkg.com/es-module-shims@1.3.6/dist/es-module-shims.js"></script>
<script type="importmap">
  {
    "imports": {
      "three": "./threejs/three.module.js",
      "three/addons/": "./threejs/examples/jsm/"
    }
  }
</script>
<script type="module">

  // -----------------------------------------
  // threejs imports
  // -----------------------------------------

  import * as THREE from 'three';
  import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';
  import { OrbitControls } from './threejs/examples/jsm/controls/OrbitControls.js';

  // -----------------------------------------
  // Setup: scene, camera, renderer
  // -----------------------------------------
  
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera( 35, window.innerWidth / window.innerHeight, 1, 2000 );
  camera.position.z = 5;
  const renderer = new THREE.WebGLRenderer({antialias: true, alpha: true});
  renderer.setSize( window.innerWidth, window.innerHeight );
  // renderer.setClearColor("#630");
  renderer.setPixelRatio( window.devicePixelRatio );
  document.body.appendChild( renderer.domElement );

  const controls = new OrbitControls( camera, renderer.domElement );
  controls.addEventListener( 'change', render ); // use if there is no animation loop
  controls.enablePan = true;
  controls.enableZoom = true;

  // -----------------------------------------
  // Add model
  // -----------------------------------------

  const modelLoader = new OBJLoader();
  modelLoader.load(
    // 'models/cave-malachite-decimate0.2.obj',
    'models/4.obj',
    // 'models/7.obj',
    // 'models/13.obj',
    // 'models/11091_FemaleHead_v4.obj',
    object => { // on loaded

      console.log(object)
      var geometry = object.children[0].geometry;
      geometry.center();

      // v1 - as is
      // scene.add(object);

      // v2 - wireframe
      let material = new THREE.MeshBasicMaterial({ color: 0xF4FF00, wireframe: true })
      let mesh = new THREE.Mesh(geometry, material)
      scene.add(mesh)

      // v3 - Points
      // let material = new THREE.PointsMaterial({ color: 0xFFFFFF, size: 0.005 })
      // let mesh = new THREE.Points(geometry, material)
      // scene.add(mesh)

      // v4 - Materials
      // const material = new THREE.MeshNormalMaterial();                   // b
      // const material = new THREE.MeshDepthMaterial();                    // c
      // const material = new THREE.MeshLambertMaterial({color: 0xFF0000}); // a
      // let mesh = new THREE.Mesh(geometry, material)
      // scene.add(mesh)

      // v5 - texture
      // var material = new THREE.MeshBasicMaterial();
      // var url = 'textures/UV_checker_Map_byValle.jpg';
      // var onLoad = function(texture) {
      //     texture.wrapS = THREE.RepeatWrapping;
      //     texture.wrapT = THREE.RepeatWrapping;
      //     texture.repeat.set(0.000200, 1);
      //     texture.offset.set(0.24995, 0);
      //     material.map = texture;
      //     // material.blending = THREE.AdditiveBlending;
      //     // material.wireframe = true;
      //     // material.color = 0x777777;
      //     // material.color = 0xffffff;
      //     material.needsUpdate = true;
      // }
      // var loader = new THREE.TextureLoader();
      // loader.load(url, onLoad);
      // let mesh = new THREE.Mesh(geometry, material)
      // scene.add(mesh)
      




      animate();

    },
    xhr => { // loading progress
      console.log( ( xhr.loaded / xhr.total * 100 ) + '% loaded' );
    },
    error => { // loading errors
      console.log( 'error loading model', error );
    }
  );

  // -----------------------------------------
  // Lights
  // -----------------------------------------

  var light = new THREE.PointLight(0xFFFFFF, 1, 500);
  light.position.set(10, 0, 25);
  scene.add(light);

  // const ambientLight = new THREE.AmbientLight( 0xcccccc, 0.4 );
  // scene.add( ambientLight );
  // const pointLight = new THREE.PointLight( 0xffffff, 0.8 );
  // camera.add( pointLight );
  // scene.add( camera );

  // -----------------------------------------
  // Render
  // -----------------------------------------

  function animate() {
    requestAnimationFrame( animate );
    render();
  }
  function render() {
    scene.rotation.y += 0.005;
    camera.lookAt( scene.position );
    renderer.render( scene, camera );
  }

  // -----------------------------------------
  // Window resize handling
  // -----------------------------------------

  window.addEventListener("resize", () => {
    renderer.setSize( window.innerWidth, window.innerHeight );
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
  });



  // -----------------------------------------
  // Fit object 
  // https://wejn.org/2020/12/cracking-the-threejs-object-fitting-nut/
  // -----------------------------------------

  const fitCameraToCenteredObject = function (camera, object, offset, orbitControls ) {
      const boundingBox = new THREE.Box3();
      boundingBox.setFromObject( object );

      var middle = new THREE.Vector3();
      var size = new THREE.Vector3();
      boundingBox.getSize(size);

      // figure out how to fit the box in the view:
      // 1. figure out horizontal FOV (on non-1.0 aspects)
      // 2. figure out distance from the object in X and Y planes
      // 3. select the max distance (to fit both sides in)
      //
      // The reason is as follows:
      //
      // Imagine a bounding box (BB) is centered at (0,0,0).
      // Camera has vertical FOV (camera.fov) and horizontal FOV
      // (camera.fov scaled by aspect, see fovh below)
      //
      // Therefore if you want to put the entire object into the field of view,
      // you have to compute the distance as: z/2 (half of Z size of the BB
      // protruding towards us) plus for both X and Y size of BB you have to
      // figure out the distance created by the appropriate FOV.
      //
      // The FOV is always a triangle:
      //
      //  (size/2)
      // +--------+
      // |       /
      // |      /
      // |     /
      // | F° /
      // |   /
      // |  /
      // | /
      // |/
      //
      // F° is half of respective FOV, so to compute the distance (the length
      // of the straight line) one has to: `size/2 / Math.tan(F)`.
      //
      // FTR, from https://threejs.org/docs/#api/en/cameras/PerspectiveCamera
      // the camera.fov is the vertical FOV.

      const fov = camera.fov * ( Math.PI / 180 );
      const fovh = 2*Math.atan(Math.tan(fov/2) * camera.aspect);
      let dx = size.z / 2 + Math.abs( size.x / 2 / Math.tan( fovh / 2 ) );
      let dy = size.z / 2 + Math.abs( size.y / 2 / Math.tan( fov / 2 ) );
      let cameraZ = Math.max(dx, dy);

      // offset the camera, if desired (to avoid filling the whole canvas)
      if( offset !== undefined && offset !== 0 ) cameraZ *= offset;

      camera.position.set( 0, 0, cameraZ );

      // set the far plane of the camera so that it easily encompasses the whole object
      const minZ = boundingBox.min.z;
      const cameraToFarEdge = ( minZ < 0 ) ? -minZ + cameraZ : cameraZ - minZ;

      camera.far = cameraToFarEdge * 3;
      camera.updateProjectionMatrix();

      if ( orbitControls !== undefined ) {
          // set camera to rotate around the center
          orbitControls.target = new THREE.Vector3(0, 0, 0);

          // prevent camera from zooming out far enough to create far plane cutoff
          orbitControls.maxDistance = cameraToFarEdge * 2;
      }
  };



</script>
</body>
</html>