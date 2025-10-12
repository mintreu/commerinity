<!-- KEEP YOUR EXISTING TEMPLATE AND STYLE -->

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'

interface Member {
  userId: number
  parentId?: number | null
  name: string
  level: string
  referral_code: string
  image?: string
  children?: Member[]
  childrenCount?: number
  depth: number
  hasChildren: boolean
}

interface Props {
  treeData: Member[]
}

const props = defineProps<Props>()
const emit = defineEmits(['selectMember'])

const threeContainer = ref<HTMLElement>()
const loading = ref(true)
const selectedMember = ref<Member | null>(null)

let scene: any, camera: any, renderer: any, controls: any
let animationId: number
let npcs: any[] = []
let cars: any[] = []
let clickableBuildings: any[] = []
let roadPaths: any[] = []

const loadThreeJS = () => {
  return new Promise((resolve, reject) => {
    if ((window as any).THREE) {
      resolve((window as any).THREE)
      return
    }

    const script = document.createElement('script')
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js'
    script.onload = () => {
      const orbitScript = document.createElement('script')
      orbitScript.src = 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js'
      orbitScript.onload = () => resolve((window as any).THREE)
      orbitScript.onerror = reject
      document.head.appendChild(orbitScript)
    }
    script.onerror = reject
    document.head.appendChild(script)
  })
}

const kingdom = computed(() => {
  const root = props.treeData.find(m => m.depth === 0)
  if (!root) return null
  return { ...root, x: 0, z: 0 }
})

const territories = computed(() => {
  if (!kingdom.value) return []

  const children = props.treeData.filter(m => m.depth === 1)
  const totalChildren = children.length
  const radius = 40

  return children.map((child, idx) => {
    const angle = (idx / totalChildren) * 2 * Math.PI
    const x = radius * Math.cos(angle)
    const z = radius * Math.sin(angle)

    const grandchildren = props.treeData.filter(m => m.depth === 2 && m.parentId === child.userId)

    const housesWithPos = grandchildren.map((gc, gIdx) => {
      const houseAngle = (gIdx / Math.max(grandchildren.length, 1)) * 2 * Math.PI
      const houseRadius = 12
      return {
        ...gc,
        relX: houseRadius * Math.cos(houseAngle),
        relZ: houseRadius * Math.sin(houseAngle)
      }
    })

    return { ...child, x, z, children: housesWithPos }
  })
})

const totalMembers = computed(() => props.treeData.length)
const totalHouses = computed(() => props.treeData.filter(m => m.depth === 2).length)

const getDefaultAvatar = (name: string) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=80&background=6366F1&color=fff&bold=true`
}

// Create road path between two points
const createRoad = (x1: number, z1: number, x2: number, z2: number) => {
  const THREE = (window as any).THREE
  const dx = x2 - x1
  const dz = z2 - z1
  const distance = Math.sqrt(dx * dx + dz * dz)
  const angle = Math.atan2(dz, dx)

  const roadGeometry = new THREE.PlaneGeometry(distance, 2)
  const roadMaterial = new THREE.MeshStandardMaterial({
    color: 0x666666,
    metalness: 0.1,
    roughness: 0.8
  })
  const road = new THREE.Mesh(roadGeometry, roadMaterial)

  road.rotation.x = -Math.PI / 2
  road.rotation.z = -angle
  road.position.set((x1 + x2) / 2, 0.05, (z1 + z2) / 2)
  road.receiveShadow = true

  // Road markings
  const markingGeometry = new THREE.PlaneGeometry(distance, 0.1)
  const markingMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff })
  const marking = new THREE.Mesh(markingGeometry, markingMaterial)
  marking.rotation.x = -Math.PI / 2
  marking.rotation.z = -angle
  marking.position.set((x1 + x2) / 2, 0.06, (z1 + z2) / 2)

  scene.add(road)
  scene.add(marking)

  return { x1, z1, x2, z2, distance }
}

// Create car
const createCar = (x: number, z: number) => {
  const THREE = (window as any).THREE
  const carGroup = new THREE.Group()

  // Car body
  const bodyGeometry = new THREE.BoxGeometry(1.5, 0.6, 0.8)
  const bodyMaterial = new THREE.MeshStandardMaterial({
    color: [0xff0000, 0x00ff00, 0x0000ff, 0xffff00, 0xff00ff][Math.floor(Math.random() * 5)],
    metalness: 0.6,
    roughness: 0.4
  })
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial)
  body.position.y = 0.3
  carGroup.add(body)

  // Car top
  const topGeometry = new THREE.BoxGeometry(0.8, 0.4, 0.7)
  const topMaterial = new THREE.MeshStandardMaterial({
    color: 0x222222,
    metalness: 0.7,
    roughness: 0.3
  })
  const top = new THREE.Mesh(topGeometry, topMaterial)
  top.position.y = 0.7
  carGroup.add(top)

  // Wheels
  const wheelGeometry = new THREE.CylinderGeometry(0.2, 0.2, 0.15, 8)
  const wheelMaterial = new THREE.MeshStandardMaterial({ color: 0x111111 })

  const positions = [
    [-0.5, 0.2, 0.5], [0.5, 0.2, 0.5],
    [-0.5, 0.2, -0.5], [0.5, 0.2, -0.5]
  ]

  positions.forEach(pos => {
    const wheel = new THREE.Mesh(wheelGeometry, wheelMaterial)
    wheel.rotation.z = Math.PI / 2
    wheel.position.set(pos[0], pos[1], pos[2])
    carGroup.add(wheel)
  })

  carGroup.position.set(x, 0, z)
  carGroup.castShadow = true

  return carGroup
}

// Create NPC
const createNPC = (x: number, z: number) => {
  const THREE = (window as any).THREE
  const npcGroup = new THREE.Group()

  // Body
  const bodyGeometry = new THREE.CylinderGeometry(0.3, 0.3, 1, 8)
  const bodyMaterial = new THREE.MeshStandardMaterial({
    color: [0x3b82f6, 0x10b981, 0xf59e0b, 0xef4444][Math.floor(Math.random() * 4)]
  })
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial)
  body.position.y = 0.5
  npcGroup.add(body)

  // Head
  const headGeometry = new THREE.SphereGeometry(0.3, 8, 8)
  const headMaterial = new THREE.MeshStandardMaterial({ color: 0xfbbf24 })
  const head = new THREE.Mesh(headGeometry, headMaterial)
  head.position.y = 1.3
  npcGroup.add(head)

  npcGroup.position.set(x, 0, z)
  npcGroup.castShadow = true

  return npcGroup
}

// Create building with label
const createBuildingWithLabel = (width: number, height: number, depth: number, color: number, position: any, name: string) => {
  const THREE = (window as any).THREE
  const geometry = new THREE.BoxGeometry(width, height, depth)
  const material = new THREE.MeshStandardMaterial({ color, metalness: 0.3, roughness: 0.7 })
  const building = new THREE.Mesh(geometry, material)
  building.position.set(position.x, height / 2, position.z)
  building.castShadow = true
  building.receiveShadow = true
  building.userData = { member: position.member, isClickable: !!position.member }

  if (name) {
    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')
    canvas.width = 256
    canvas.height = 64

    if (context) {
      context.fillStyle = 'rgba(255, 255, 255, 0.9)'
      context.fillRect(0, 0, canvas.width, canvas.height)
      context.fillStyle = '#1f2937'
      context.font = 'bold 20px Arial'
      context.textAlign = 'center'
      context.textBaseline = 'middle'
      context.fillText(name.substring(0, 15), canvas.width / 2, canvas.height / 2)
    }

    const texture = new THREE.CanvasTexture(canvas)
    const spriteMaterial = new THREE.SpriteMaterial({ map: texture })
    const sprite = new THREE.Sprite(spriteMaterial)
    sprite.scale.set(width * 1.5, width * 0.4, 1)
    sprite.position.set(position.x, height + 2, position.z)
    scene.add(sprite)
  }

  return building
}

// Create roof
const createRoof = (size: number, color: number, position: any, yOffset: number) => {
  const THREE = (window as any).THREE
  const geometry = new THREE.ConeGeometry(size, size * 0.8, 4)
  const material = new THREE.MeshStandardMaterial({ color, metalness: 0.2, roughness: 0.8 })
  const roof = new THREE.Mesh(geometry, material)
  roof.position.set(position.x, yOffset, position.z)
  roof.rotation.y = Math.PI / 4
  roof.castShadow = true
  return roof
}

const initThreeScene = async () => {
  const THREE = (window as any).THREE
  if (!threeContainer.value || !THREE) return

  scene = new THREE.Scene()
  scene.background = new THREE.Color(0xe0e7ff)
  scene.fog = new THREE.Fog(0xe0e7ff, 50, 200)

  camera = new THREE.PerspectiveCamera(50, threeContainer.value.clientWidth / threeContainer.value.clientHeight, 0.1, 1000)
  camera.position.set(60, 50, 60)

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(threeContainer.value.clientWidth, threeContainer.value.clientHeight)
  renderer.setPixelRatio(window.devicePixelRatio)
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  threeContainer.value.appendChild(renderer.domElement)

  controls = new (THREE as any).OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.05
  controls.minDistance = 20
  controls.maxDistance = 150
  controls.maxPolarAngle = Math.PI / 2.2

  // Lights
  scene.add(new THREE.AmbientLight(0xffffff, 0.6))

  const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8)
  directionalLight.position.set(50, 80, 50)
  directionalLight.castShadow = true
  directionalLight.shadow.mapSize.width = 2048
  directionalLight.shadow.mapSize.height = 2048
  directionalLight.shadow.camera.near = 0.5
  directionalLight.shadow.camera.far = 200
  directionalLight.shadow.camera.left = -100
  directionalLight.shadow.camera.right = 100
  directionalLight.shadow.camera.top = 100
  directionalLight.shadow.camera.bottom = -100
  scene.add(directionalLight)

  scene.add(new THREE.HemisphereLight(0x87ceeb, 0x8b7355, 0.4))

  // Ground
  const groundGeometry = new THREE.CircleGeometry(100, 64)
  const groundMaterial = new THREE.MeshStandardMaterial({ color: 0x6ee7b7, metalness: 0, roughness: 0.9 })
  const ground = new THREE.Mesh(groundGeometry, groundMaterial)
  ground.rotation.x = -Math.PI / 2
  ground.receiveShadow = true
  scene.add(ground)

  // Water
  const waterGeometry = new THREE.PlaneGeometry(80, 15)
  const waterMaterial = new THREE.MeshStandardMaterial({ color: 0x60a5fa, metalness: 0.6, roughness: 0.2, transparent: true, opacity: 0.7 })
  const water = new THREE.Mesh(waterGeometry, waterMaterial)
  water.rotation.x = -Math.PI / 2
  water.position.set(0, 0.1, -40)
  scene.add(water)

  clickableBuildings = []
  roadPaths = []

  // Kingdom
  if (kingdom.value) {
    const kingdomBuilding = createBuildingWithLabel(10, 15, 10, 0x8b5cf6,
        { x: kingdom.value.x, z: kingdom.value.z, member: kingdom.value }, kingdom.value.name)
    scene.add(kingdomBuilding)
    clickableBuildings.push(kingdomBuilding)
    scene.add(createRoof(7, 0xc026d3, { x: kingdom.value.x, z: kingdom.value.z }, 15))

    const tower1 = createBuildingWithLabel(3, 20, 3, 0x7c3aed,
        { x: kingdom.value.x - 6, z: kingdom.value.z - 6, member: kingdom.value }, '')
    const tower2 = createBuildingWithLabel(3, 20, 3, 0x7c3aed,
        { x: kingdom.value.x + 6, z: kingdom.value.z - 6, member: kingdom.value }, '')
    scene.add(tower1, tower2)
    clickableBuildings.push(tower1, tower2)
    scene.add(createRoof(2.5, 0xe11d48, { x: kingdom.value.x - 6, z: kingdom.value.z - 6 }, 20))
    scene.add(createRoof(2.5, 0xe11d48, { x: kingdom.value.x + 6, z: kingdom.value.z - 6 }, 20))
  }

  // Territories & Roads
  territories.value.forEach((territory, idx) => {
    const territoryBuilding = createBuildingWithLabel(6, 8, 6, 0xfb923c,
        { x: territory.x, z: territory.z, member: territory }, territory.name)
    scene.add(territoryBuilding)
    clickableBuildings.push(territoryBuilding)
    scene.add(createRoof(5, 0xdc2626, { x: territory.x, z: territory.z }, 8))

    // Road from kingdom to territory
    if (kingdom.value) {
      const road = createRoad(kingdom.value.x, kingdom.value.z, territory.x, territory.z)
      roadPaths.push(road)
    }

    // Houses & Roads
    territory.children?.forEach(house => {
      const houseBuilding = createBuildingWithLabel(3, 4, 3, 0x4ade80,
          { x: territory.x + house.relX, z: territory.z + house.relZ, member: house }, house.name)
      scene.add(houseBuilding)
      clickableBuildings.push(houseBuilding)
      scene.add(createRoof(2.5, 0xb91c1c, { x: territory.x + house.relX, z: territory.z + house.relZ }, 4))

      // Road from territory to house
      const houseRoad = createRoad(territory.x, territory.z, territory.x + house.relX, territory.z + house.relZ)
      roadPaths.push(houseRoad)
    })
  })

  // Trees
  for (let i = 0; i < 20; i++) {
    const angle = Math.random() * Math.PI * 2
    const distance = 50 + Math.random() * 30
    const x = Math.cos(angle) * distance
    const z = Math.sin(angle) * distance

    const trunk = createBuildingWithLabel(0.8, 4, 0.8, 0x78350f, { x, z, member: null }, '')
    scene.add(trunk)

    const foliage = new THREE.SphereGeometry(2.5, 8, 8)
    const foliageMesh = new THREE.Mesh(foliage, new THREE.MeshStandardMaterial({ color: 0x22c55e }))
    foliageMesh.position.set(x, 5, z)
    foliageMesh.castShadow = true
    scene.add(foliageMesh)
  }

  // Create Cars on roads
  cars = []
  roadPaths.forEach((road, idx) => {
    if (idx % 2 === 0) { // Car on every other road
      const car = createCar(road.x1, road.z1)
      scene.add(car)
      cars.push({
        mesh: car,
        roadIndex: idx,
        progress: 0,
        speed: 0.01 + Math.random() * 0.01
      })
    }
  })

  // Create NPCs on roads
  npcs = []
  roadPaths.forEach((road, idx) => {
    if (idx % 3 === 0) { // NPC on every third road
      const npc = createNPC(road.x1, road.z1)
      scene.add(npc)
      npcs.push({
        mesh: npc,
        roadIndex: idx,
        progress: Math.random(),
        speed: 0.003 + Math.random() * 0.002,
        waitTime: 0
      })
    }
  })

  // Click detection
  const raycaster = new THREE.Raycaster()
  const mouse = new THREE.Vector2()

  const onMouseClick = (event: MouseEvent) => {
    if (!threeContainer.value) return

    const rect = threeContainer.value.getBoundingClientRect()
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

    raycaster.setFromCamera(mouse, camera)
    const intersects = raycaster.intersectObjects(clickableBuildings, false)

    if (intersects.length > 0) {
      const clickedObject = intersects[0].object
      if (clickedObject.userData.isClickable && clickedObject.userData.member) {
        selectedMember.value = clickedObject.userData.member
        emit('selectMember', clickedObject.userData.member)
      }
    }
  }

  renderer.domElement.addEventListener('click', onMouseClick)

  loading.value = false
  animate()
}

const animate = () => {
  animationId = requestAnimationFrame(animate)

  // Update cars
  cars.forEach(car => {
    if (car.roadIndex < roadPaths.length) {
      const road = roadPaths[car.roadIndex]
      car.progress += car.speed

      if (car.progress >= 1) {
        car.progress = 0
      }

      const x = road.x1 + (road.x2 - road.x1) * car.progress
      const z = road.z1 + (road.z2 - road.z1) * car.progress
      car.mesh.position.set(x, 0, z)

      const angle = Math.atan2(road.z2 - road.z1, road.x2 - road.x1)
      car.mesh.rotation.y = -angle + Math.PI / 2
    }
  })

  // Update NPCs
  npcs.forEach(npc => {
    if (npc.waitTime > 0) {
      npc.waitTime--
    } else {
      if (npc.roadIndex < roadPaths.length) {
        const road = roadPaths[npc.roadIndex]
        npc.progress += npc.speed

        if (npc.progress >= 1) {
          npc.progress = 0
          npc.waitTime = Math.random() * 120
        }

        const x = road.x1 + (road.x2 - road.x1) * npc.progress
        const z = road.z1 + (road.z2 - road.z1) * npc.progress
        npc.mesh.position.set(x, 0, z)

        const angle = Math.atan2(road.z2 - road.z1, road.x2 - road.x1)
        npc.mesh.rotation.y = -angle + Math.PI / 2
      }
    }
  })

  if (controls) controls.update()
  if (renderer && scene && camera) renderer.render(scene, camera)
}

const resetCamera = () => {
  if (!camera || !controls) return
  camera.position.set(60, 50, 60)
  controls.target.set(0, 0, 0)
  controls.update()
}

const setTopView = () => {
  if (!camera || !controls) return
  camera.position.set(0, 100, 0.1)
  controls.target.set(0, 0, 0)
  controls.update()
}

const setIsometricView = () => {
  if (!camera || !controls) return
  camera.position.set(60, 50, 60)
  controls.target.set(0, 0, 0)
  controls.update()
}

const handleResize = () => {
  if (!threeContainer.value || !camera || !renderer) return
  camera.aspect = threeContainer.value.clientWidth / threeContainer.value.clientHeight
  camera.updateProjectionMatrix()
  renderer.setSize(threeContainer.value.clientWidth, threeContainer.value.clientHeight)
}

onMounted(async () => {
  await loadThreeJS()
  await nextTick()
  await initThreeScene()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  if (animationId) cancelAnimationFrame(animationId)
  if (renderer && threeContainer.value) {
    threeContainer.value.removeChild(renderer.domElement)
  }
  if (controls) controls.dispose()
  if (renderer) renderer.dispose()
})

watch(() => props.treeData, async (newData) => {
  if (newData && newData.length > 0 && scene) {
    while(scene.children.length > 0) {
      scene.remove(scene.children[0])
    }
    await initThreeScene()
  }
}, { deep: true })
</script>
