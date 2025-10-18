<template>
  <div class="village-root">
    <div v-show="isInWorld" class="world-container" :class="[timeOfDay, { 'fullscreen-mode': isFullscreen }]">
      <div ref="threeContainer" class="world-canvas"></div>

      <!-- In-Game Building Popup -->
      <transition name="popup-scale">
        <div v-if="selectedBuilding" class="in-game-popup">
          <div class="game-modal">
            <div class="flex items-center gap-4 mb-4">
              <span class="text-6xl">{{ selectedBuilding.icon }}</span>
              <div class="flex-1">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ selectedBuilding.name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedBuilding.description }}</p>
                <div class="mt-2 inline-flex items-center px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full text-sm font-semibold">
                  Level {{ selectedBuilding.level }}
                </div>
              </div>
            </div>

            <div class="flex gap-3">
              <button @click="enterBuilding(selectedBuilding)"
                      class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold text-lg shadow-lg transition-all hover:scale-105"
                      :disabled="selectedBuilding.status !== 'active'">
                <Icon name="heroicons:arrow-right-on-rectangle" class="w-6 h-6" />
                <span>{{ selectedBuilding.cta }}</span>
              </button>
              <button @click="closeModal"
                      class="px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Member/House Info Popup -->
      <transition name="popup-scale">
        <div v-if="selectedMember" class="in-game-popup">
          <div class="game-modal">
            <div class="flex items-center gap-4 mb-4">
              <img :src="selectedMember.image || getDefaultAvatar(selectedMember.name)"
                   class="w-20 h-20 rounded-full border-4 border-indigo-500 shadow-lg" />
              <div class="flex-1">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ selectedMember.name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedMember.level }} • Depth {{ selectedMember.depth }}</p>
                <div class="mt-2 inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-sm font-semibold">
                  {{ selectedMember.hasChildren ? `${selectedMember.childrenCount || 0} Team Members` : 'No Team' }}
                </div>
              </div>
            </div>

            <div class="flex gap-3">
              <button @click="viewMemberProfile"
                      class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold text-lg shadow-lg transition-all hover:scale-105">
                <Icon name="heroicons:user" class="w-6 h-6" />
                <span>View Profile</span>
              </button>
              <button @click="closeModal"
                      class="px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all">
                Close
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Header -->
      <div class="world-header">
        <div class="flex items-center gap-3">
          <img :src="kingdomLeader?.image || getDefaultAvatar(kingdomLeader?.name || 'User')"
               class="w-12 h-12 rounded-full border-2 border-white/30 shadow-lg" />
          <div>
            <div class="text-sm font-bold text-white">{{ websiteName }}</div>
            <div class="text-xs text-white/70">{{ kingdomLeader?.name }}'s Archipelago</div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click="toggleFullscreen" class="fullscreen-btn">
            <Icon v-if="!isFullscreen" name="heroicons:arrows-pointing-out" class="w-5 h-5" />
            <Icon v-else name="heroicons:arrows-pointing-in" class="w-5 h-5" />
          </button>
          <button @click="exitWorld" class="exit-btn">
            <Icon name="heroicons:x-mark" class="w-5 h-5" />
            Exit
          </button>
        </div>
      </div>

      <!-- Time -->
      <div class="time-indicator">
        <div class="glass-panel px-3 py-2">
          <div class="flex items-center gap-2 text-xs">
            <span>{{ timeOfDay === 'day' ? '☀️' : '🌙' }}</span>
            <span class="text-gray-700 dark:text-gray-300 font-medium">
              {{ timeOfDay === 'day' ? 'Day' : 'Night' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Mini Map -->
      <div class="minimap-panel">
        <div class="glass-panel p-2">
          <div class="text-[10px] font-bold text-gray-700 dark:text-gray-300 mb-1 text-center">Archipelago Map</div>
          <div class="w-32 h-32 bg-blue-300 dark:bg-blue-900 rounded relative overflow-hidden border-2 border-blue-400 dark:border-blue-700">
            <!-- Main island -->
            <div class="absolute w-4 h-4 bg-red-600 rounded-full shadow-lg" style="left: 62px; top: 62px;"></div>
            <!-- Other islands -->
            <div v-for="island in allIslands" :key="`island-${island.id}`"
                 class="absolute rounded-full shadow-md"
                 :class="island.depth === 1 ? 'w-3 h-3 bg-green-500' : 'w-2 h-2 bg-yellow-500'"
                 :style="minimapIslandPos(island)"></div>
            <!-- Ships -->
            <div v-for="(ship, idx) in ships" :key="`ship-${idx}`"
                 class="absolute w-1.5 h-1.5 bg-white rounded-sm"
                 :style="minimapShipPos(ship)"></div>
            <!-- Camera -->
            <div class="absolute w-2 h-2 bg-blue-600 rounded-full animate-pulse shadow-lg"
                 :style="minimapCameraPos()"></div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-panel">
        <div class="glass-panel p-3">
          <div class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">World Stats</div>
          <div class="space-y-1 text-xs">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Citizens:</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ totalMembers }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Islands:</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ allIslands.length + 1 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Ships:</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ ships.length }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">NPCs:</span>
              <span class="font-bold text-gray-900 dark:text-white">{{ npcCount }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Controls -->
      <div class="controls-panel">
        <div class="glass-panel p-3">
          <div class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Controls</div>
          <div class="space-y-1 text-[10px] text-gray-600 dark:text-gray-400">
            <div>🖱️ Drag: Rotate</div>
            <div>📜 Scroll: Zoom</div>
            <div><kbd>WASD</kbd> Move</div>
            <div><kbd>Q/E</kbd> Rotate</div>
            <div>🖱️ Click: Interact</div>
          </div>
          <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 flex gap-1">
            <button @click="resetCamera" class="mini-btn" title="Reset View">
              <Icon name="heroicons:arrows-pointing-in" class="w-3 h-3" />
            </button>
            <button @click="setTopView" class="mini-btn text-[10px]">Top</button>
            <button @click="setKingdomView" class="mini-btn text-[10px]">Castle</button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <transition name="fade">
        <div v-if="loading" class="loading-overlay">
          <div class="glass-panel p-6 flex flex-col items-center gap-3">
            <Icon name="heroicons:arrow-path" class="w-8 h-8 text-indigo-600 animate-spin" />
            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Building Archipelago...</span>
          </div>
        </div>
      </transition>

      <!-- Building Enter Animation -->
      <transition name="enter-building">
        <div v-if="isEnteringBuilding" class="enter-animation">
          <div class="enter-content">
            <Icon name="heroicons:arrow-right-on-rectangle" class="w-16 h-16 text-white animate-pulse" />
            <p class="text-white text-lg font-semibold mt-4">Entering {{ enteringBuildingName }}...</p>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { BUILDINGS } from '~/composables/buildingConfig'

interface Member {
  userId: number; parentId?: number | null; name: string; level: string; referral_code: string
  image?: string; children?: Member[]; childrenCount?: number; depth: number; hasChildren: boolean
}

interface BuildingConfig {
  id: string; name: string; icon: string; description: string; route: string; category: string
  color: number; position: { x: number, z: number }; status: 'active' | 'disabled' | 'coming_soon'
  level: number; maxLevel: number; score: number; upgradeable: boolean; requiredUserLevel?: number
  cta: string; hasModal: boolean; modalId?: string; size: { width: number, height: number, depth: number }
}

interface Island {
  id: number; chief: Member; members: Member[]; position: { x: number, y: number, z: number }
  depth: number; radius: number; landHeight: number; portPosition: { x: number, z: number }
}

interface Ship {
  mesh: any; fromIsland: Island | null; toIsland: Island | null; progress: number; speed: number
}

interface Props { treeData: Member[]; skipSplash?: boolean; autoEnter?: boolean }

const props = withDefaults(defineProps<Props>(), { skipSplash: false, autoEnter: false })
const emit = defineEmits(['selectMember', 'upgradeBuilding', 'exitWorld'])
const config = useRuntimeConfig()
const router = useRouter()

// Refs
const threeContainer = ref<HTMLElement>()
const loading = ref(true)
const isInWorld = ref(props.autoEnter)
const isFullscreen = ref(false)
const selectedBuilding = ref<BuildingConfig | null>(null)
const selectedMember = ref<Member | null>(null)
const timeOfDay = ref<'day' | 'night'>('day')
const allIslands = ref<Island[]>([])
const ships = ref<Ship[]>([])
const npcCount = ref(0)
const isEnteringBuilding = ref(false)
const enteringBuildingName = ref('')

// Computed
const websiteName = computed(() => config.public.websiteName || 'Archipelago')
const kingdomLeader = computed(() => props.treeData.find(m => m.depth === 0))
const buildings = computed(() => BUILDINGS)
const totalMembers = computed(() => props.treeData.length)

// Three.js
let scene: any, camera: any, renderer: any, controls: any, animationId: number, dayNightInterval: any
let clickableObjects: any[] = [], keys: any = {}, npcs: any[] = [], animals: any[] = [], birds: any[] = []
let mainIslandPort: { x: number, z: number } = { x: 0, z: 0 }

const getDefaultAvatar = (name: string) => `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=80&background=6366F1&color=fff&bold=true`

// Exit/Toggle
const exitWorld = () => {
  emit('exitWorld')
  isInWorld.value = false
  if (animationId) cancelAnimationFrame(animationId)
  if (dayNightInterval) clearInterval(dayNightInterval)
  if (renderer && threeContainer.value && renderer.domElement.parentNode) threeContainer.value.removeChild(renderer.domElement)
  if (controls) controls.dispose()
  if (renderer) renderer.dispose()
  selectedBuilding.value = null
  selectedMember.value = null
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
  if (renderer && camera && threeContainer.value) {
    nextTick(() => {
      const width = threeContainer.value!.clientWidth, height = threeContainer.value!.clientHeight
      camera.aspect = width / height
      camera.updateProjectionMatrix()
      renderer.setSize(width, height)
    })
  }
}

const closeModal = () => {
  selectedBuilding.value = null
  selectedMember.value = null
}

const enterBuilding = (building: BuildingConfig) => {
  if (building.status !== 'active') return
  isEnteringBuilding.value = true
  enteringBuildingName.value = building.name
  setTimeout(() => {
    router.push(building.route)
    isEnteringBuilding.value = false
  }, 1500)
}

const viewMemberProfile = () => {
  if (selectedMember.value) {
    emit('selectMember', selectedMember.value)
    closeModal()
  }
}

// Day/Night
const startDayNightCycle = () => {
  dayNightInterval = setInterval(() => {
    timeOfDay.value = timeOfDay.value === 'day' ? 'night' : 'day'
    updateLighting()
  }, 15 * 60 * 1000)
}

const updateLighting = () => {
  if (!scene) return
  const isDay = timeOfDay.value === 'day'
  scene.background.setHex(isDay ? 0x87CEEB : 0x1a1a2e)
  scene.fog.color.setHex(isDay ? 0x87CEEB : 0x1a1a2e)
  scene.children.forEach((child: any) => {
    if (child.isDirectionalLight) child.intensity = isDay ? 1.2 : 0.5
    if (child.isAmbientLight) child.intensity = isDay ? 0.8 : 0.4
  })
}

// Load Three.js
const loadThreeJS = () => new Promise((resolve, reject) => {
  if ((window as any).THREE) { resolve((window as any).THREE); return }
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

// Init Scene
const initThreeScene = async () => {
  const THREE = (window as any).THREE
  if (!threeContainer.value || !THREE) return

  console.log('🏝️ Building Archipelago World...')

  // Scene
  scene = new THREE.Scene()
  scene.background = new THREE.Color(0x87CEEB)
  scene.fog = new THREE.Fog(0x87CEEB, 300, 800)

  // Camera
  const width = threeContainer.value.clientWidth, height = threeContainer.value.clientHeight
  camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 2000)
  camera.position.set(200, 150, 200)

  // Renderer
  renderer = new THREE.WebGLRenderer({ antialias: true })
  renderer.setSize(width, height)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.shadowMap.enabled = true
  renderer.shadowMap.type = THREE.PCFSoftShadowMap
  threeContainer.value.appendChild(renderer.domElement)

  // Controls
  controls = new (THREE as any).OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.05
  controls.minDistance = 60
  controls.maxDistance = 500
  controls.maxPolarAngle = Math.PI / 2 - 0.05
  controls.minPolarAngle = Math.PI / 10

  // Lighting
  scene.add(new THREE.AmbientLight(0xffffff, 0.8))
  const dirLight = new THREE.DirectionalLight(0xffffff, 1.2)
  dirLight.position.set(150, 200, 150)
  dirLight.castShadow = true
  dirLight.shadow.mapSize.width = 4096
  dirLight.shadow.mapSize.height = 4096
  dirLight.shadow.camera.left = -300
  dirLight.shadow.camera.right = 300
  dirLight.shadow.camera.top = 300
  dirLight.shadow.camera.bottom = -300
  scene.add(dirLight)

  // Ocean
  const waterGeo = new THREE.CircleGeometry(600, 128)
  const waterMat = new THREE.MeshStandardMaterial({
    color: 0x1e90ff,
    roughness: 0.3,
    metalness: 0.7,
    transparent: true,
    opacity: 0.95
  })
  const water = new THREE.Mesh(waterGeo, waterMat)
  water.rotation.x = -Math.PI / 2
  water.position.y = 0
  water.receiveShadow = true
  scene.add(water)

  // Build World
  buildArchipelago(THREE)

  // Life
  generateNPCs(THREE)
  generateAnimals(THREE)
  generateBirds(THREE)

  // Interactions
  setupInteractions(THREE)
  setupKeyboard()

  console.log('✅ Archipelago Ready!')
  loading.value = false
  animate()
  startDayNightCycle()
}

// Build Archipelago
const buildArchipelago = (THREE: any) => {
  const kingdom = kingdomLeader.value
  if (!kingdom) return

  // Main Island (center)
  createMainIsland(kingdom, THREE)

  // Build Islands
  const depthMap: { [depth: number]: Member[] } = {}
  props.treeData.forEach(member => {
    if (!depthMap[member.depth]) depthMap[member.depth] = []
    depthMap[member.depth].push(member)
  })

  Object.keys(depthMap).forEach(depthStr => {
    const depth = parseInt(depthStr)
    if (depth === 0) return

    const members = depthMap[depth]
    const parentGroups: { [parentId: number]: Member[] } = {}
    members.forEach(member => {
      const parentId = member.parentId || 0
      if (!parentGroups[parentId]) parentGroups[parentId] = []
      parentGroups[parentId].push(member)
    })

    Object.keys(parentGroups).forEach((parentIdStr, groupIdx) => {
      const parentId = parseInt(parentIdStr)
      const groupMembers = parentGroups[parentId]
      const chief = groupMembers[0]

      const baseRadius = 150 + (depth - 1) * 80
      const groupCount = Object.keys(parentGroups).length
      const angle = (groupIdx / Math.max(groupCount, 1)) * Math.PI * 2
      const x = Math.cos(angle) * baseRadius
      const z = Math.sin(angle) * baseRadius

      // Port position (facing towards main island)
      const portAngle = Math.atan2(-z, -x)
      const portRadius = 28
      const portX = x + Math.cos(portAngle) * portRadius
      const portZ = z + Math.sin(portAngle) * portRadius

      const island: Island = {
        id: chief.userId,
        chief,
        members: groupMembers.slice(1, 6),
        position: { x, y: 0, z },
        depth,
        radius: 30 + (groupMembers.length * 2),
        landHeight: 12,
        portPosition: { x: portX, z: portZ }
      }

      allIslands.value.push(island)
      createIsland(island, THREE)
    })
  })

  // Create Ships
  createShips(THREE)
}

// Create Main Island (Castle Island)
const createMainIsland = (kingdom: Member, THREE: any) => {
  const radius = 100
  const landHeight = 50

  // Island base
  const islandGeo = new THREE.CylinderGeometry(radius, radius - 10, landHeight, 64)
  const islandMat = new THREE.MeshStandardMaterial({ color: 0x8B7355, roughness: 0.9 })
  const island = new THREE.Mesh(islandGeo, islandMat)
  island.position.y = landHeight / 2
  island.receiveShadow = true
  island.castShadow = true
  scene.add(island)

  // Grass top
  const grassGeo = new THREE.CircleGeometry(radius, 64)
  const grassMat = new THREE.MeshStandardMaterial({ color: 0x90EE90 })
  const grass = new THREE.Mesh(grassGeo, grassMat)
  grass.rotation.x = -Math.PI / 2
  grass.position.y = landHeight + 0.1
  grass.receiveShadow = true
  scene.add(grass)

  // Castle
  const castleGroup = new THREE.Group()
  const castleGeo = new THREE.BoxGeometry(25, 30, 25)
  const castleMat = new THREE.MeshStandardMaterial({ color: 0xA0A0A0, roughness: 0.7 })
  const castle = new THREE.Mesh(castleGeo, castleMat)
  castle.position.y = 15
  castle.castShadow = true
  castle.receiveShadow = true
  castleGroup.add(castle)

  // Towers
  for (let i = 0; i < 4; i++) {
    const towerGeo = new THREE.CylinderGeometry(3.5, 3.5, 12, 16)
    const towerMat = new THREE.MeshStandardMaterial({ color: 0x707070 })
    const tower = new THREE.Mesh(towerGeo, towerMat)
    const angle = (i / 4) * Math.PI * 2
    tower.position.set(Math.cos(angle) * 15, 36, Math.sin(angle) * 15)
    tower.castShadow = true
    castleGroup.add(tower)

    const roofGeo = new THREE.ConeGeometry(4.5, 6, 16)
    const roofMat = new THREE.MeshStandardMaterial({ color: 0x8b4513 })
    const roof = new THREE.Mesh(roofGeo, roofMat)
    roof.position.set(Math.cos(angle) * 15, 45, Math.sin(angle) * 15)
    roof.castShadow = true
    castleGroup.add(roof)
  }

  castleGroup.position.y = landHeight
  castleGroup.userData = { type: 'member', data: kingdom }
  clickableObjects.push(castle)
  scene.add(castleGroup)

  // Buildings around castle
  buildings.value.forEach((b, idx) => {
    const angle = (idx / buildings.value.length) * Math.PI * 2
    const dist = 60
    b.position = { x: Math.cos(angle) * dist, z: Math.sin(angle) * dist }
    const building = createGroundBuilding(b, landHeight, THREE)
    scene.add(building)
    clickableObjects.push(building)
  })

  // Port (south side)
  mainIslandPort = { x: 0, z: radius + 5 }
  createPort(mainIslandPort, landHeight, THREE)

  // Trees
  for (let i = 0; i < 20; i++) {
    const angle = Math.random() * Math.PI * 2
    const dist = 40 + Math.random() * 40
    const x = Math.cos(angle) * dist
    const z = Math.sin(angle) * dist
    const tree = createTree({ x, y: landHeight, z }, THREE)
    scene.add(tree)
  }
}

// Create Island
const createIsland = (island: Island, THREE: any) => {
  const landHeight = island.landHeight

  // Island base
  const islandGeo = new THREE.CylinderGeometry(island.radius, island.radius - 5, landHeight, 32)
  const islandMat = new THREE.MeshStandardMaterial({ color: 0xC4A484, roughness: 0.9 })
  const islandMesh = new THREE.Mesh(islandGeo, islandMat)
  islandMesh.position.set(island.position.x, landHeight / 2, island.position.z)
  islandMesh.receiveShadow = true
  islandMesh.castShadow = true
  scene.add(islandMesh)

  // Grass top
  const grassGeo = new THREE.CircleGeometry(island.radius, 32)
  const grassMat = new THREE.MeshStandardMaterial({ color: 0x90EE90 })
  const grass = new THREE.Mesh(grassGeo, grassMat)
  grass.rotation.x = -Math.PI / 2
  grass.position.set(island.position.x, landHeight, island.position.z)
  grass.receiveShadow = true
  scene.add(grass)

  // Port
  createPort(island.portPosition, landHeight, THREE)

  // Road from port to center
  createIslandRoad(island.portPosition, { x: island.position.x, z: island.position.z }, landHeight, THREE)

  // Chief house
  const chiefHouse = createHouse(island.chief, { x: island.position.x, y: landHeight, z: island.position.z }, THREE, true)
  scene.add(chiefHouse)
  clickableObjects.push(chiefHouse)

  // Village buildings (for depth 1 islands)
  if (island.depth === 1) {
    const villageBuildings = [
      { name: 'Village Store', icon: '🏪', color: 0x22c55e, offset: { x: -12, z: 0 } },
      { name: 'Bank', icon: '🏦', color: 0x3b82f6, offset: { x: 12, z: 0 } }
    ]

    villageBuildings.forEach(vb => {
      const bx = island.position.x + vb.offset.x
      const bz = island.position.z + vb.offset.z
      const vBuilding = createSimpleBuilding(vb, { x: bx, y: landHeight, z: bz }, THREE)
      scene.add(vBuilding)
    })
  }

  // Member houses
  island.members.forEach((member, idx) => {
    const angle = (idx / island.members.length) * Math.PI * 2
    const houseRadius = island.radius * 0.65
    const x = island.position.x + Math.cos(angle) * houseRadius
    const z = island.position.z + Math.sin(angle) * houseRadius

    const house = createHouse(member, { x, y: landHeight, z }, THREE, false)
    scene.add(house)
    clickableObjects.push(house)

    // Road from house to center
    createIslandRoad({ x: island.position.x, z: island.position.z }, { x, z }, landHeight, THREE)
  })

  // Trees
  for (let i = 0; i < 8; i++) {
    const angle = (i / 8) * Math.PI * 2 + Math.PI / 16
    const treeRadius = island.radius * 0.88
    const x = island.position.x + Math.cos(angle) * treeRadius
    const z = island.position.z + Math.sin(angle) * treeRadius
    const tree = createTree({ x, y: landHeight, z }, THREE)
    scene.add(tree)
  }
}

// Create Port
const createPort = (position: { x: number, z: number }, landHeight: number, THREE: any) => {
  // Dock
  const dockGeo = new THREE.BoxGeometry(10, 1, 15)
  const dockMat = new THREE.MeshStandardMaterial({ color: 0x8B4513 })
  const dock = new THREE.Mesh(dockGeo, dockMat)
  dock.position.set(position.x, landHeight - 0.5, position.z)
  dock.castShadow = true
  dock.receiveShadow = true
  scene.add(dock)

  // Pillars
  for (let i = 0; i < 4; i++) {
    const pillarGeo = new THREE.CylinderGeometry(0.5, 0.5, landHeight, 8)
    const pillarMat = new THREE.MeshStandardMaterial({ color: 0x654321 })
    const pillar = new THREE.Mesh(pillarGeo, pillarMat)
    pillar.position.set(
        position.x + (i < 2 ? -4 : 4),
        landHeight / 2,
        position.z + (i % 2 === 0 ? -6 : 6)
    )
    pillar.castShadow = true
    scene.add(pillar)
  }
}

// Create Ships
const createShips = (THREE: any) => {
  const shipCount = Math.min(Math.max(allIslands.value.length, 3), 8)

  for (let i = 0; i < shipCount; i++) {
    const ship = createShip(THREE)
    scene.add(ship)

    const fromIsland = i === 0 ? null : allIslands.value[Math.floor(Math.random() * allIslands.value.length)]
    const toIsland = allIslands.value[Math.floor(Math.random() * allIslands.value.length)]

    // Start at port
    if (fromIsland) {
      ship.position.set(fromIsland.portPosition.x, 2, fromIsland.portPosition.z)
    } else {
      ship.position.set(mainIslandPort.x, 2, mainIslandPort.z)
    }

    ships.value.push({
      mesh: ship,
      fromIsland,
      toIsland,
      progress: 0,
      speed: 0.002 + Math.random() * 0.003
    })
  }
}

// Create Ship
const createShip = (THREE: any) => {
  const group = new THREE.Group()

  // Hull
  const hullGeo = new THREE.BoxGeometry(4, 2, 12)
  const hullMat = new THREE.MeshStandardMaterial({ color: 0x8B4513 })
  const hull = new THREE.Mesh(hullGeo, hullMat)
  hull.position.y = 0.5
  hull.castShadow = true
  group.add(hull)

  // Deck
  const deckGeo = new THREE.BoxGeometry(3.5, 0.5, 11)
  const deckMat = new THREE.MeshStandardMaterial({ color: 0xD2691E })
  const deck = new THREE.Mesh(deckGeo, deckMat)
  deck.position.y = 1.75
  deck.castShadow = true
  group.add(deck)

  // Mast
  const mastGeo = new THREE.CylinderGeometry(0.3, 0.3, 10, 8)
  const mastMat = new THREE.MeshStandardMaterial({ color: 0x654321 })
  const mast = new THREE.Mesh(mastGeo, mastMat)
  mast.position.y = 7
  mast.castShadow = true
  group.add(mast)

  // Sail
  const sailGeo = new THREE.PlaneGeometry(6, 8)
  const sailMat = new THREE.MeshStandardMaterial({ color: 0xFFFAF0, side: THREE.DoubleSide })
  const sail = new THREE.Mesh(sailGeo, sailMat)
  sail.position.set(3, 7, 0)
  sail.rotation.y = Math.PI / 2
  sail.castShadow = true
  group.add(sail)

  return group
}

// Create House
const createHouse = (member: Member, pos: any, THREE: any, isChief: boolean) => {
  const group = new THREE.Group()
  const size = isChief ? 9 : 5
  const height = isChief ? 8 : 5

  const houseGeo = new THREE.BoxGeometry(size, height, size)
  const houseColor = isChief ? 0xFF6B6B : 0x4f46e5
  const houseMat = new THREE.MeshStandardMaterial({ color: houseColor, roughness: 0.6 })
  const house = new THREE.Mesh(houseGeo, houseMat)
  house.position.y = height / 2
  house.castShadow = true
  house.receiveShadow = true
  group.add(house)

  // Windows
  const windowGeo = new THREE.PlaneGeometry(1.5, 1.5)
  const windowMat = new THREE.MeshBasicMaterial({ color: 0xFFFFAA })
  for (let i = 0; i < 2; i++) {
    const window1 = new THREE.Mesh(windowGeo, windowMat)
    window1.position.set(-1.5 + i * 3, height / 2, size / 2 + 0.01)
    group.add(window1)
  }

  // Door
  const doorGeo = new THREE.PlaneGeometry(1.8, 3)
  const doorMat = new THREE.MeshStandardMaterial({ color: 0x8B4513 })
  const door = new THREE.Mesh(doorGeo, doorMat)
  door.position.set(0, -0.5, size / 2 + 0.01)
  group.add(door)

  // Roof
  const roofSize = isChief ? 7 : 4
  const roofHeight = isChief ? 5 : 3
  const roofGeo = new THREE.ConeGeometry(roofSize, roofHeight, 4)
  const roofMat = new THREE.MeshStandardMaterial({ color: 0x8b4513 })
  const roof = new THREE.Mesh(roofGeo, roofMat)
  roof.position.y = height + roofHeight / 2
  roof.rotation.y = Math.PI / 4
  roof.castShadow = true
  group.add(roof)

  // Chimney
  const chimneyGeo = new THREE.BoxGeometry(1, 2, 1)
  const chimneyMat = new THREE.MeshStandardMaterial({ color: 0x8B4513 })
  const chimney = new THREE.Mesh(chimneyGeo, chimneyMat)
  chimney.position.set(size / 3, height + 1.5, 0)
  chimney.castShadow = true
  group.add(chimney)

  group.position.set(pos.x, pos.y, pos.z)
  group.userData = { type: 'member', data: member }
  return group
}

// Create Simple Building
const createSimpleBuilding = (config: any, pos: any, THREE: any) => {
  const group = new THREE.Group()
  const buildingGeo = new THREE.BoxGeometry(8, 6, 8)
  const buildingMat = new THREE.MeshStandardMaterial({ color: config.color, roughness: 0.7 })
  const building = new THREE.Mesh(buildingGeo, buildingMat)
  building.position.y = 3
  building.castShadow = true
  building.receiveShadow = true
  group.add(building)

  // Sign
  const canvas = document.createElement('canvas')
  canvas.width = 128
  canvas.height = 64
  const ctx = canvas.getContext('2d')
  if (ctx) {
    ctx.fillStyle = 'white'
    ctx.fillRect(0, 0, 128, 64)
    ctx.font = 'bold 24px Arial'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillText(config.icon, 64, 32)
  }
  const signTex = new THREE.CanvasTexture(canvas)
  const signGeo = new THREE.PlaneGeometry(4, 2)
  const signMat = new THREE.MeshBasicMaterial({ map: signTex })
  const sign = new THREE.Mesh(signGeo, signMat)
  sign.position.set(0, 3, 4.01)
  group.add(sign)

  group.position.set(pos.x, pos.y, pos.z)
  return group
}

// Create Building
const createGroundBuilding = (config: BuildingConfig, groundY: number, THREE: any) => {
  const group = new THREE.Group()
  const buildingGeo = new THREE.BoxGeometry(config.size.width, config.size.height, config.size.depth)
  const buildingColor = config.status === 'active' ? config.color : 0x666666
  const buildingMat = new THREE.MeshStandardMaterial({ color: buildingColor, roughness: 0.7 })
  const building = new THREE.Mesh(buildingGeo, buildingMat)
  building.position.y = config.size.height / 2
  building.castShadow = true
  building.receiveShadow = true
  group.add(building)

  // Icon
  const canvas = document.createElement('canvas')
  canvas.width = 256
  canvas.height = 256
  const ctx = canvas.getContext('2d')
  if (ctx) {
    ctx.fillStyle = `#${buildingColor.toString(16).padStart(6, '0')}`
    ctx.fillRect(0, 0, 256, 256)
    ctx.font = 'bold 120px Arial'
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillStyle = 'white'
    ctx.fillText(config.icon, 128, 128)
  }
  const texture = new THREE.CanvasTexture(canvas)
  const signGeo = new THREE.PlaneGeometry(config.size.width * 0.8, config.size.height * 0.6)
  const signMat = new THREE.MeshBasicMaterial({ map: texture })
  const sign = new THREE.Mesh(signGeo, signMat)
  sign.position.set(0, config.size.height / 4, config.size.depth / 2 + 0.01)
  group.add(sign)

  group.position.set(config.position.x, groundY, config.position.z)
  group.userData = { type: 'building', data: config }
  return group
}

// Create Island Road
const createIslandRoad = (from: any, to: any, groundY: number, THREE: any) => {
  const direction = new THREE.Vector3(to.x - from.x, 0, to.z - from.z)
  const distance = direction.length()
  const roadGeo = new THREE.PlaneGeometry(3, distance)
  const roadMat = new THREE.MeshStandardMaterial({ color: 0x666666, roughness: 0.9 })
  const road = new THREE.Mesh(roadGeo, roadMat)
  road.rotation.x = -Math.PI / 2
  road.rotation.z = Math.atan2(direction.z, direction.x) - Math.PI / 2
  road.position.set((from.x + to.x) / 2, groundY + 0.01, (from.z + to.z) / 2)
  road.receiveShadow = true
  scene.add(road)
}

// Create Tree
const createTree = (pos: any, THREE: any) => {
  const group = new THREE.Group()
  const trunkGeo = new THREE.CylinderGeometry(0.4, 0.5, 4, 8)
  const trunkMat = new THREE.MeshStandardMaterial({ color: 0x78350f })
  const trunk = new THREE.Mesh(trunkGeo, trunkMat)
  trunk.position.y = 2
  trunk.castShadow = true
  group.add(trunk)

  const foliageGeo = new THREE.SphereGeometry(2, 12, 12)
  const foliageMat = new THREE.MeshStandardMaterial({ color: 0x22c55e })
  const foliage = new THREE.Mesh(foliageGeo, foliageMat)
  foliage.position.y = 5
  foliage.castShadow = true
  group.add(foliage)

  group.position.set(pos.x, pos.y, pos.z)
  return group
}

// Generate NPCs (2x houses)
const generateNPCs = (THREE: any) => {
  const count = Math.min(totalMembers.value * 2, 80)
  npcCount.value = count

  for (let i = 0; i < count; i++) {
    const island = allIslands.value[Math.floor(Math.random() * allIslands.value.length)]
    if (!island) continue

    const angle = Math.random() * Math.PI * 2
    const dist = Math.random() * (island.radius - 5)
    const x = island.position.x + Math.cos(angle) * dist
    const z = island.position.z + Math.sin(angle) * dist

    const npc = createNPC(THREE, i)
    npc.position.set(x, island.landHeight, z)
    scene.add(npc)
    npcs.push({ mesh: npc, target: { x, z }, speed: 0.03 + Math.random() * 0.05, island })
  }
}

// Create NPC
const createNPC = (THREE: any, index: number) => {
  const group = new THREE.Group()
  const headGeo = new THREE.SphereGeometry(0.4, 8, 8)
  const headMat = new THREE.MeshStandardMaterial({ color: 0xFFDBB5 })
  const head = new THREE.Mesh(headGeo, headMat)
  head.position.y = 1.6
  head.castShadow = true
  group.add(head)

  const bodyGeo = new THREE.CylinderGeometry(0.3, 0.4, 1, 8)
  const bodyColors = [0xFF0000, 0x00FF00, 0x0000FF, 0xFFFF00, 0xFF00FF]
  const bodyMat = new THREE.MeshStandardMaterial({ color: bodyColors[index % bodyColors.length] })
  const body = new THREE.Mesh(bodyGeo, bodyMat)
  body.position.y = 0.9
  body.castShadow = true
  group.add(body)

  const legGeo = new THREE.CylinderGeometry(0.15, 0.15, 0.8, 8)
  const legMat = new THREE.MeshStandardMaterial({ color: 0x333333 })
  const leg1 = new THREE.Mesh(legGeo, legMat)
  leg1.position.set(-0.2, 0.2, 0)
  leg1.castShadow = true
  group.add(leg1)

  const leg2 = new THREE.Mesh(legGeo, legMat)
  leg2.position.set(0.2, 0.2, 0)
  leg2.castShadow = true
  group.add(leg2)

  return group
}

// Generate Animals
const generateAnimals = (THREE: any) => {
  const count = Math.min(Math.floor(totalMembers.value / 2), 20)

  for (let i = 0; i < count; i++) {
    const island = allIslands.value[Math.floor(Math.random() * allIslands.value.length)]
    if (!island) continue

    const angle = Math.random() * Math.PI * 2
    const dist = Math.random() * (island.radius - 8)
    const x = island.position.x + Math.cos(angle) * dist
    const z = island.position.z + Math.sin(angle) * dist

    const isCow = i % 2 === 0
    const animal = isCow ? createCow(THREE) : createGoat(THREE)
    animal.position.set(x, island.landHeight, z)
    scene.add(animal)
    animals.push({ mesh: animal, target: { x, z }, speed: 0.02 + Math.random() * 0.03, island })
  }
}

// Create Cow
const createCow = (THREE: any) => {
  const group = new THREE.Group()
  const bodyGeo = new THREE.BoxGeometry(2, 1.5, 3)
  const bodyMat = new THREE.MeshStandardMaterial({ color: 0xFFFFFF })
  const body = new THREE.Mesh(bodyGeo, bodyMat)
  body.position.y = 1
  body.castShadow = true
  group.add(body)

  const headGeo = new THREE.BoxGeometry(1, 1, 1.2)
  const headMat = new THREE.MeshStandardMaterial({ color: 0xFFFFFF })
  const head = new THREE.Mesh(headGeo, headMat)
  head.position.set(0, 1.2, 2)
  head.castShadow = true
  group.add(head)

  const legGeo = new THREE.CylinderGeometry(0.2, 0.2, 1, 8)
  const legMat = new THREE.MeshStandardMaterial({ color: 0x8B4513 })
  for (let i = 0; i < 4; i++) {
    const leg = new THREE.Mesh(legGeo, legMat)
    leg.position.set(i < 2 ? -0.6 : 0.6, 0.3, i % 2 === 0 ? -1 : 1)
    leg.castShadow = true
    group.add(leg)
  }

  return group
}

// Create Goat
const createGoat = (THREE: any) => {
  const group = new THREE.Group()
  const bodyGeo = new THREE.BoxGeometry(1.5, 1.2, 2)
  const bodyMat = new THREE.MeshStandardMaterial({ color: 0xD2691E })
  const body = new THREE.Mesh(bodyGeo, bodyMat)
  body.position.y = 0.8
  body.castShadow = true
  group.add(body)

  const headGeo = new THREE.BoxGeometry(0.8, 0.8, 1)
  const headMat = new THREE.MeshStandardMaterial({ color: 0xD2691E })
  const head = new THREE.Mesh(headGeo, headMat)
  head.position.set(0, 1, 1.5)
  head.castShadow = true
  group.add(head)

  const hornGeo = new THREE.ConeGeometry(0.1, 0.5, 6)
  const hornMat = new THREE.MeshStandardMaterial({ color: 0xFFFFFF })
  for (let i = 0; i < 2; i++) {
    const horn = new THREE.Mesh(hornGeo, hornMat)
    horn.position.set(i === 0 ? -0.3 : 0.3, 1.5, 1.5)
    horn.rotation.z = (i === 0 ? -1 : 1) * Math.PI / 6
    horn.castShadow = true
    group.add(horn)
  }

  const legGeo = new THREE.CylinderGeometry(0.15, 0.15, 0.8, 8)
  const legMat = new THREE.MeshStandardMaterial({ color: 0x8B4513 })
  for (let i = 0; i < 4; i++) {
    const leg = new THREE.Mesh(legGeo, legMat)
    leg.position.set(i < 2 ? -0.5 : 0.5, 0.2, i % 2 === 0 ? -0.7 : 0.7)
    leg.castShadow = true
    group.add(leg)
  }

  return group
}

// Generate Birds
const generateBirds = (THREE: any) => {
  for (let i = 0; i < 10; i++) {
    const bird = createBird(THREE)
    bird.position.set(Math.random() * 300 - 150, 30 + Math.random() * 30, Math.random() * 300 - 150)
    scene.add(bird)
    birds.push({ mesh: bird, speed: 0.3 + Math.random() * 0.3, direction: Math.random() * Math.PI * 2, height: 30 + Math.random() * 30 })
  }
}

// Create Bird
const createBird = (THREE: any) => {
  const group = new THREE.Group()
  const bodyGeo = new THREE.SphereGeometry(0.3, 8, 8)
  const bodyMat = new THREE.MeshStandardMaterial({ color: 0xFF6B6B })
  const body = new THREE.Mesh(bodyGeo, bodyMat)
  body.castShadow = true
  group.add(body)

  const wingGeo = new THREE.BoxGeometry(1.5, 0.1, 0.5)
  const wingMat = new THREE.MeshStandardMaterial({ color: 0xFF6B6B })
  const wings = new THREE.Mesh(wingGeo, wingMat)
  wings.position.y = 0.2
  wings.castShadow = true
  group.add(wings)

  return group
}

// Interactions
const setupInteractions = (THREE: any) => {
  const raycaster = new THREE.Raycaster()
  const mouse = new THREE.Vector2()

  const onClick = (event: MouseEvent) => {
    if (!threeContainer.value) return
    const rect = threeContainer.value.getBoundingClientRect()
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

    raycaster.setFromCamera(mouse, camera)
    const intersects = raycaster.intersectObjects(clickableObjects, true)

    if (intersects.length > 0) {
      const obj = intersects[0].object
      let userData = obj.userData
      if (!userData.type && obj.parent) userData = obj.parent.userData
      if (!userData.type && obj.parent?.parent) userData = obj.parent.parent.userData

      if (userData.type === 'building') {
        selectedBuilding.value = userData.data
        selectedMember.value = null
      } else if (userData.type === 'member') {
        selectedMember.value = userData.data
        selectedBuilding.value = null
      }
    }
  }

  renderer.domElement.addEventListener('click', onClick)
}

// Keyboard
const setupKeyboard = () => {
  document.addEventListener('keydown', (e) => { keys[e.key.toLowerCase()] = true })
  document.addEventListener('keyup', (e) => { keys[e.key.toLowerCase()] = false })
}

const handleKeyboard = () => {
  if (!camera || !controls) return
  const speed = 2
  const forward = new THREE.Vector3()
  camera.getWorldDirection(forward)
  forward.y = 0
  forward.normalize()
  const right = new THREE.Vector3().crossVectors(forward, new THREE.Vector3(0, 1, 0))

  if (keys['w'] || keys['arrowup']) { camera.position.addScaledVector(forward, speed); controls.target.addScaledVector(forward, speed) }
  if (keys['s'] || keys['arrowdown']) { camera.position.addScaledVector(forward, -speed); controls.target.addScaledVector(forward, -speed) }
  if (keys['a'] || keys['arrowleft']) { camera.position.addScaledVector(right, -speed); controls.target.addScaledVector(right, -speed) }
  if (keys['d'] || keys['arrowright']) { camera.position.addScaledVector(right, speed); controls.target.addScaledVector(right, speed) }
  if (keys['q']) {
    const offset = camera.position.clone().sub(controls.target)
    offset.applyAxisAngle(new THREE.Vector3(0, 1, 0), 0.05)
    camera.position.copy(controls.target).add(offset)
  }
  if (keys['e']) {
    const offset = camera.position.clone().sub(controls.target)
    offset.applyAxisAngle(new THREE.Vector3(0, 1, 0), -0.05)
    camera.position.copy(controls.target).add(offset)
  }
}

// Animate
const animate = () => {
  animationId = requestAnimationFrame(animate)
  if (controls) controls.update()
  handleKeyboard()

  const time = Date.now() * 0.001

  // Animate NPCs
  npcs.forEach(npc => {
    const targetAngle = Math.atan2(npc.target.z - npc.mesh.position.z, npc.target.x - npc.mesh.position.x)
    npc.mesh.position.x += Math.cos(targetAngle) * npc.speed
    npc.mesh.position.z += Math.sin(targetAngle) * npc.speed
    npc.mesh.rotation.y = targetAngle + Math.PI / 2

    if (Math.random() < 0.01 && npc.island) {
      const angle = Math.random() * Math.PI * 2
      const dist = Math.random() * (npc.island.radius - 5)
      npc.target = { x: npc.island.position.x + Math.cos(angle) * dist, z: npc.island.position.z + Math.sin(angle) * dist }
    }
  })

  // Animate animals
  animals.forEach(animal => {
    const targetAngle = Math.atan2(animal.target.z - animal.mesh.position.z, animal.target.x - animal.mesh.position.x)
    animal.mesh.position.x += Math.cos(targetAngle) * animal.speed
    animal.mesh.position.z += Math.sin(targetAngle) * animal.speed
    animal.mesh.rotation.y = targetAngle

    if (Math.random() < 0.008 && animal.island) {
      const angle = Math.random() * Math.PI * 2
      const dist = Math.random() * (animal.island.radius - 8)
      animal.target = { x: animal.island.position.x + Math.cos(angle) * dist, z: animal.island.position.z + Math.sin(angle) * dist }
    }
  })

  // Animate birds
  birds.forEach(bird => {
    bird.mesh.position.x += Math.cos(bird.direction) * bird.speed
    bird.mesh.position.z += Math.sin(bird.direction) * bird.speed
    bird.mesh.position.y = bird.height + Math.sin(time * 2) * 2
    bird.mesh.rotation.y = bird.direction
    bird.mesh.children[1].rotation.z = Math.sin(time * 5) * 0.3

    if (Math.abs(bird.mesh.position.x) > 350 || Math.abs(bird.mesh.position.z) > 350) {
      bird.direction = Math.random() * Math.PI * 2
    }
  })

  // Animate ships
  ships.value.forEach(ship => {
    ship.progress += ship.speed

    if (ship.progress >= 1) {
      ship.progress = 0
      ship.fromIsland = ship.toIsland
      ship.toIsland = allIslands.value[Math.floor(Math.random() * allIslands.value.length)] || null
    }

    const from = ship.fromIsland ? ship.fromIsland.portPosition : mainIslandPort
    const to = ship.toIsland ? ship.toIsland.portPosition : mainIslandPort

    const x = from.x + (to.x - from.x) * ship.progress
    const z = from.z + (to.z - from.z) * ship.progress

    ship.mesh.position.x = x
    ship.mesh.position.z = z
    ship.mesh.position.y = 2 + Math.sin(time + ship.progress * 10) * 0.3

    const angle = Math.atan2(to.z - from.z, to.x - from.x)
    ship.mesh.rotation.y = angle - Math.PI / 2
  })

  if (renderer && scene && camera) renderer.render(scene, camera)
}

// Camera
const resetCamera = () => { if (camera && controls) { camera.position.set(200, 150, 200); controls.target.set(0, 0, 0); controls.update() } }
const setTopView = () => { if (camera && controls) { camera.position.set(0, 400, 0.1); controls.target.set(0, 0, 0); controls.update() } }
const setKingdomView = () => { if (camera && controls) { camera.position.set(0, 120, 120); controls.target.set(0, 50, 0); controls.update() } }

// Minimap
const minimapIslandPos = (island: Island) => {
  const scale = 128 / 1200
  const x = (island.position.x * scale + 64) + 'px'
  const y = (island.position.z * scale + 64) + 'px'
  return { left: x, top: y }
}

const minimapShipPos = (ship: Ship) => {
  const scale = 128 / 1200
  const x = (ship.mesh.position.x * scale + 64) + 'px'
  const y = (ship.mesh.position.z * scale + 64) + 'px'
  return { left: x, top: y }
}

const minimapCameraPos = () => {
  if (!camera) return { left: '64px', top: '64px' }
  const scale = 128 / 1200
  const x = (camera.position.x * scale + 64) + 'px'
  const y = (camera.position.z * scale + 64) + 'px'
  return { left: x, top: y }
}

// Lifecycle
onMounted(async () => {
  if (props.autoEnter) {
    await nextTick()
    await loadThreeJS()
    await nextTick()
    await initThreeScene()
  }
})

onUnmounted(() => {
  if (dayNightInterval) clearInterval(dayNightInterval)
  if (animationId) cancelAnimationFrame(animationId)
  if (renderer && threeContainer.value && renderer.domElement.parentNode) threeContainer.value.removeChild(renderer.domElement)
  if (controls) controls.dispose()
  if (renderer) renderer.dispose()
  document.removeEventListener('keydown', () => {})
  document.removeEventListener('keyup', () => {})
})
</script>

<style scoped>
.village-root { @apply relative w-full h-full; }
.world-container { @apply relative w-full h-full overflow-hidden transition-colors duration-1000; touch-action: none; min-height: 600px; }
.world-container.fullscreen-mode { @apply fixed inset-0 z-[9999]; min-height: 100vh; }
.world-container.day { @apply bg-gradient-to-b from-sky-300 via-cyan-200 to-blue-100; }
.world-container.night { @apply bg-gradient-to-b from-gray-900 via-indigo-950 to-gray-800; }
.world-canvas { @apply absolute inset-0 w-full h-full; }
.world-header { @apply absolute top-4 left-4 right-4 z-50 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-3 shadow-2xl; }
.fullscreen-btn { @apply flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-medium text-sm transition-all; }
.exit-btn { @apply flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white font-medium text-sm transition-all; }
.time-indicator { @apply absolute top-4 left-1/2 -translate-x-1/2 z-40; }
.minimap-panel { @apply absolute bottom-4 right-4 z-40; }
.controls-panel { @apply absolute top-24 right-4 z-40; }
.stats-panel { @apply absolute top-24 left-4 z-40; }
.glass-panel { @apply bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-xl shadow-xl border border-white/20; }
.mini-btn { @apply flex-1 px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium transition-all hover:bg-indigo-100 text-[10px]; }
.loading-overlay { @apply absolute inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50; }
.in-game-popup { @apply fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-md; }
.game-modal { @apply bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-lg w-full mx-4 border-4 border-indigo-500; }
.enter-animation { @apply fixed inset-0 z-[110] flex items-center justify-center bg-black/80 backdrop-blur-lg; }
.enter-content { @apply flex flex-col items-center; }
.popup-scale-enter-active, .popup-scale-leave-active { transition: all 0.3s ease; }
.popup-scale-enter-from, .popup-scale-leave-to { opacity: 0; transform: scale(0.8); }
.enter-building-enter-active, .enter-building-leave-active { transition: all 0.5s ease; }
.enter-building-enter-from, .enter-building-leave-to { opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
kbd { @apply font-mono bg-gray-200 dark:bg-gray-700 px-1 rounded text-[9px]; }
@media (max-width: 768px) {
  .world-header { @apply top-2 left-2 right-2 p-2 text-xs; }
  .controls-panel, .stats-panel { @apply top-16; }
  .minimap-panel { @apply bottom-2 right-2; }
  .game-modal { @apply p-6; }
}
</style>
