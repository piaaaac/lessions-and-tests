<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>threejs basics</title>
<link rel="stylesheet" href="https://unpkg.com/minimal-css-reset@1.1.0/reset.min.css">
</head>
<body>

<script async src="https://unpkg.com/es-module-shims@1.3.6/dist/es-module-shims.js"></script>
<script type="importmap">
  {
    "imports": {
      "three": "https://unpkg.com/three/build/three.module.js"
    }
  }
</script>
<script type="module">

  // -----------------------------------------
  // threejs imports
  // -----------------------------------------

  import * as THREE from 'three';
  // import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';
  // import { OrbitControls } from './threejs/examples/jsm/controls/OrbitControls.js';

  // -----------------------------------------
  // Setup: scene, camera, renderer
  // -----------------------------------------
  
  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera( 75, window.innerWidth / window.innerHeight, 0.1, 1000 );
  camera.position.z = 5;
  const renderer = new THREE.WebGLRenderer();
  renderer.setSize( window.innerWidth, window.innerHeight );
  renderer.setClearColor("#666");
  document.body.appendChild( renderer.domElement );

  // -----------------------------------------
  // Add an element using: geometry, material
  // -----------------------------------------

  const geometry = new THREE.SphereGeometry(1, 10, 10);
  const material = new THREE.MeshLambertMaterial({color: 0xFF0000});
  const mesh = new THREE.Mesh(geometry, material);
  scene.add(mesh);

  // -----------------------------------------
  // Add a light
  // -----------------------------------------

  var light = new THREE.PointLight(0xFFFFFF, 1, 500);
  light.position.set(10, 0, 25);
  scene.add(light);

  // -----------------------------------------
  // Render
  // -----------------------------------------

  var render = function () {
    requestAnimationFrame(render);
    renderer.render(scene, camera);
  }
  render();

  // -----------------------------------------
  // Window resize handling
  // -----------------------------------------

  window.addEventListener("resize", () => {
    renderer.setSize( window.innerWidth, window.innerHeight );
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
  });

</script>
</body>
</html>