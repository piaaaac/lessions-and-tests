
<!---------------------------------------------------------
--  Basic Threejs interactivity w/ raycasting
--  via https://www.youtube.com/watch?v=6oFvqLfRnsU&t=886s
----------------------------------------------------------->

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>threejs basics</title>
<link rel="stylesheet" href="https://unpkg.com/minimal-css-reset@1.1.0/reset.min.css">
</head>
<body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js" integrity="sha512-f8mwTB+Bs8a5c46DEm7HQLcJuHMBaH/UFlcgyetMqqkvTcYg4g5VXsYR71b3qC82lZytjNYvBj2pf0VekA9/FQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

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
  const renderer = new THREE.WebGLRenderer({antialias: true});
  renderer.setSize( window.innerWidth, window.innerHeight );
  renderer.setClearColor("#666");
  document.body.appendChild( renderer.domElement );

  // -----------------------------------------
  // Add an element using: geometry, material
  // -----------------------------------------

  const geometry = new THREE.BoxGeometry(1, 1, 1);
  // const mesh = new THREE.Mesh(geometry, material);
  // scene.add(mesh);

  for (var i = 0; i<15;i++) {
    const material = new THREE.MeshLambertMaterial({color: 0xffff00});
    var mesh = new THREE.Mesh(geometry, material);
    mesh.position.x = (Math.random() - 0.5) * 10;
    mesh.position.y = (Math.random() - 0.5) * 10;
    mesh.position.z = (Math.random() - 0.5) * 10;
    scene.add(mesh);
  }

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
  // Raycaster & interactivity
  // -----------------------------------------

  const raycaster = new THREE.Raycaster();
  const mouse = new THREE.Vector2();

  window.addEventListener("mousemove", (event) => {
    event.preventDefault();
    mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
    raycaster.setFromCamera(mouse, camera);

    var intersects = raycaster.intersectObjects(scene.children, true);
    intersects.forEach(intersect => {
      intersect.object.userData.hovered = true;
      intersect.object.material.color.set("cyan");
      var tl = new TimelineMax()
      tl.to(intersect.object.rotation, 3, {x: -Math.PI * 0.25, y: -Math.PI * 0.25, ease: Expo.easeOut})
    });
    scene.children.forEach(child => {
      var isIntersecting = intersects.map(intersect => (intersect.object.uuid)).includes(child.uuid);
      if (child.userData.hovered && !isIntersecting) {
        child.userData.hovered = false;
        child.material.color.set("yellow");
        var tl = new TimelineMax()
        tl.to(child.rotation, 3, {x: 0, y: 0, ease: Expo.easeOut})
      }
    })
    document.body.style.cursor = (intersects.length > 0) ? "pointer" : "auto"
  })

  window.addEventListener("click", (event) => {
    event.preventDefault();
    mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
    raycaster.setFromCamera(mouse, camera);

    var intersects = raycaster.intersectObjects(scene.children, true);
    intersects.forEach(intersect => {
      var tl = new TimelineMax()
      tl.to(intersect.object.position, 3, {
        x: intersect.object.position.x + (Math.random() - 0.5) * 4,
        y: intersect.object.position.y + (Math.random() - 0.5) * 4,
        z: intersect.object.position.z + (Math.random() - 0.5) * 4,
        ease: Expo.easeOut,
      })
    });
  })

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