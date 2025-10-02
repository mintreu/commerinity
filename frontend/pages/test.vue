<template>
  <div class="min-h-screen bg-gradient-to-br from-yellow-900 via-orange-900 to-pink-900 p-4">
    <!-- Header with User Stats -->
    <div class="max-w-md mx-auto mb-6">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-4">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
              <Icon name="heroicons:user" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h2 class="text-white font-semibold">{{ user.name }}</h2>
              <p class="text-white/70 text-sm">Level {{ user.level }}</p>
            </div>
          </div>
          <div class="text-right">
            <div class="flex items-center space-x-2 text-yellow-400">
              <Icon name="heroicons:star" class="w-5 h-5" />
              <span class="font-bold">{{ user.points }}</span>
            </div>
            <p class="text-white/70 text-sm">Points</p>
          </div>
        </div>

        <!-- Daily Spins Counter -->
        <div class="flex justify-between items-center bg-white/10 rounded-xl p-3">
          <span class="text-white">Daily Spins</span>
          <span class="text-yellow-400 font-bold">{{ dailySpins }}/{{ maxDailySpins }}</span>
        </div>
      </div>
    </div>

    <!-- Spin Wheel Container -->
    <div class="max-w-md mx-auto mb-6">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 relative">
        <!-- Wheel Canvas -->
        <div class="relative flex justify-center mb-6">
          <canvas
              ref="wheelCanvas"
              width="300"
              height="300"
              class="drop-shadow-2xl"
          ></canvas>

          <!-- Pointer Arrow -->
          <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-2 z-10">
            <div class="w-0 h-0 border-l-4 border-r-4 border-b-8 border-l-transparent border-r-transparent border-b-white"></div>
          </div>
        </div>

        <!-- Spin Button -->
        <div class="text-center">
          <button
              @click="spinWheel"
              :disabled="isSpinning || dailySpins >= maxDailySpins"
              class="bg-gradient-to-r from-yellow-500 to-orange-600 text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all duration-300 hover:from-yellow-600 hover:to-orange-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
          >
            {{ isSpinning ? 'Spinning...' : dailySpins >= maxDailySpins ? 'No Spins Left' : 'SPIN NOW!' }}
          </button>

          <!-- Watch Ad for Extra Spin -->
          <div v-if="dailySpins >= maxDailySpins" class="mt-4">
            <button
                @click="watchAdForSpin"
                class="bg-gradient-to-r from-purple-500 to-pink-600 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:from-purple-600 hover:to-pink-700 active:scale-95"
            >
              <Icon name="heroicons:play" class="w-5 h-5 inline mr-2" />
              Watch Ad for Extra Spin
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Wins -->
    <div class="max-w-md mx-auto mb-6">
      <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-4">
        <h3 class="text-white font-semibold mb-3 flex items-center">
          <Icon name="heroicons:trophy" class="w-5 h-5 mr-2 text-yellow-400" />
          Recent Wins
        </h3>
        <div class="space-y-2 max-h-32 overflow-y-auto">
          <div
              v-for="win in recentWins"
              :key="win.id"
              class="flex items-center justify-between bg-white/10 rounded-lg p-2"
          >
            <div class="flex items-center space-x-2">
              <span class="text-2xl">{{ win.emoji }}</span>
              <span class="text-white text-sm">{{ win.prize }}</span>
            </div>
            <span class="text-yellow-400 text-sm">+{{ win.points }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Prize Won Modal -->
    <div
        v-if="showPrizeModal"
        class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50"
        @click.self="closePrizeModal"
    >
      <div
          ref="prizeModal"
          class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl p-6 max-w-sm w-full text-center transform-gpu"
      >
        <div class="text-6xl mb-4 animate-bounce">{{ currentPrize?.emoji }}</div>
        <h2 class="text-2xl font-bold text-white mb-2">Congratulations!</h2>
        <p class="text-white/90 mb-4">You won {{ currentPrize?.prize }}!</p>
        <div class="flex items-center justify-center space-x-2 mb-6">
          <Icon name="heroicons:star" class="w-6 h-6 text-white" />
          <span class="text-white font-bold text-xl">+{{ currentPrize?.points }} Points</span>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
          <button
              v-if="currentPrize?.type === 'coupon'"
              @click="claimCoupon"
              class="w-full bg-green-500 text-white py-3 px-6 rounded-xl font-semibold transition-all duration-300 hover:bg-green-600 active:scale-95"
          >
            Claim Coupon
          </button>

          <button
              @click="closePrizeModal"
              class="w-full bg-white/20 text-white py-3 px-6 rounded-xl font-semibold transition-all duration-300 hover:bg-white/30 active:scale-95"
          >
            Continue Playing
          </button>
        </div>
      </div>
    </div>

    <!-- Ad Modal -->
    <div
        v-if="showAdModal"
        class="fixed inset-0 bg-black/90 flex items-center justify-center p-4 z-50"
    >
      <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Watch Advertisement</h3>

        <!-- Simulated Ad Content -->
        <div class="bg-gray-200 rounded-xl p-8 mb-4 relative">
          <div class="text-4xl mb-2">📱</div>
          <p class="text-gray-600">Sample Advertisement</p>
          <p class="text-sm text-gray-500">{{ adCountdown }}s remaining</p>

          <!-- Skip button (appears after 5 seconds) -->
          <button
              v-if="canSkipAd"
              @click="skipAd"
              class="absolute top-2 right-2 bg-gray-600 text-white px-2 py-1 rounded text-xs"
          >
            Skip
          </button>
        </div>

        <p class="text-gray-600 text-sm">Watch the full ad to earn an extra spin!</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { gsap } from 'gsap'

// SEO
useSeoMeta({
  title: 'Lucky Spin Wheel - Win Amazing Prizes & Coupons',
  description: 'Spin the lucky wheel daily to win exciting prizes, discount coupons, and points. Play now and claim your rewards!'
})

// Types
interface Prize {
  id: string
  prize: string
  emoji: string
  points: number
  probability: number
  color: string
  type: 'points' | 'coupon' | 'bonus'
  couponCode?: string
  couponValue?: number
}

interface User {
  name: string
  level: number
  points: number
}

interface RecentWin {
  id: string
  prize: string
  emoji: string
  points: number
  timestamp: number
}

// Game State
const wheelCanvas = ref<HTMLCanvasElement | null>(null)
const prizeModal = ref<HTMLElement | null>(null)
const user = ref<User>({
  name: 'Player',
  level: 1,
  points: 1250
})

const dailySpins = ref(0)
const maxDailySpins = ref(3)
const isSpinning = ref(false)
const showPrizeModal = ref(false)
const showAdModal = ref(false)
const currentPrize = ref<Prize | null>(null)
const recentWins = ref<RecentWin[]>([])
const adCountdown = ref(30)
const canSkipAd = ref(false)

// Prize Configuration
const prizes = ref<Prize[]>([
  { id: '1', prize: '10% Off Coupon', emoji: '🎫', points: 50, probability: 20, color: '#FF6B6B', type: 'coupon', couponCode: 'SPIN10', couponValue: 10 },
  { id: '2', prize: '100 Points', emoji: '⭐', points: 100, probability: 25, color: '#4ECDC4', type: 'points' },
  { id: '3', prize: 'Free Shipping', emoji: '🚚', points: 75, probability: 15, color: '#45B7D1', type: 'coupon', couponCode: 'FREESHIP' },
  { id: '4', prize: '50 Points', emoji: '💎', points: 50, probability: 30, color: '#96CEB4', type: 'points' },
  { id: '5', prize: '20% Off Coupon', emoji: '🎁', points: 100, probability: 8, color: '#FFEAA7', type: 'coupon', couponCode: 'SPIN20', couponValue: 20 },
  { id: '6', prize: 'Better Luck!', emoji: '🍀', points: 10, probability: 2, color: '#DDA0DD', type: 'points' }
])

let wheel: any = null
let adInterval: NodeJS.Timeout | null = null

// Lifecycle
onMounted(() => {
  loadUserData()
  initializeWheel()
  loadRecentWins()
})

onUnmounted(() => {
  if (adInterval) clearInterval(adInterval)
})

// Game Logic
function initializeWheel() {
  if (!wheelCanvas.value) return

  const canvas = wheelCanvas.value
  const ctx = canvas.getContext('2d')!

  wheel = new SpinWheel(canvas, ctx, prizes.value)
  wheel.draw()
}

class SpinWheel {
  private canvas: HTMLCanvasElement
  private ctx: CanvasRenderingContext2D
  private prizes: Prize[]
  private currentRotation: number = 0
  private isSpinning: boolean = false

  constructor(canvas: HTMLCanvasElement, ctx: CanvasRenderingContext2D, prizes: Prize[]) {
    this.canvas = canvas
    this.ctx = ctx
    this.prizes = prizes
  }

  draw() {
    const centerX = this.canvas.width / 2
    const centerY = this.canvas.height / 2
    const radius = 140

    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)
    this.ctx.save()
    this.ctx.translate(centerX, centerY)
    this.ctx.rotate(this.currentRotation)

    const segmentAngle = (2 * Math.PI) / this.prizes.length

    this.prizes.forEach((prize, index) => {
      const startAngle = index * segmentAngle
      const endAngle = startAngle + segmentAngle

      // Draw segment
      this.ctx.beginPath()
      this.ctx.arc(0, 0, radius, startAngle, endAngle)
      this.ctx.lineTo(0, 0)
      this.ctx.fillStyle = prize.color
      this.ctx.fill()
      this.ctx.strokeStyle = '#fff'
      this.ctx.lineWidth = 2
      this.ctx.stroke()

      // Draw text
      this.ctx.save()
      this.ctx.rotate(startAngle + segmentAngle / 2)
      this.ctx.textAlign = 'right'
      this.ctx.fillStyle = '#fff'
      this.ctx.font = 'bold 14px Inter'
      this.ctx.fillText(prize.emoji, radius - 20, -5)
      this.ctx.font = '12px Inter'
      this.ctx.fillText(prize.prize, radius - 20, 10)
      this.ctx.restore()
    })

    // Draw center circle
    this.ctx.beginPath()
    this.ctx.arc(0, 0, 20, 0, 2 * Math.PI)
    this.ctx.fillStyle = '#fff'
    this.ctx.fill()
    this.ctx.strokeStyle = '#333'
    this.ctx.lineWidth = 3
    this.ctx.stroke()

    this.ctx.restore()
  }

  spin(targetPrize: Prize): Promise<void> {
    return new Promise((resolve) => {
      this.isSpinning = true

      const targetIndex = this.prizes.findIndex(p => p.id === targetPrize.id)
      const segmentAngle = (2 * Math.PI) / this.prizes.length
      const targetAngle = (targetIndex * segmentAngle) + (segmentAngle / 2)
      const spinAmount = Math.PI * 2 * 5 + (2 * Math.PI - targetAngle) // 5 full rotations plus target

      gsap.to(this, {
        currentRotation: this.currentRotation + spinAmount,
        duration: 4,
        ease: "power4.out",
        onUpdate: () => this.draw(),
        onComplete: () => {
          this.isSpinning = false
          resolve()
        }
      })
    })
  }
}

function selectPrizeBasedOnProbability(): Prize {
  const random = Math.random() * 100
  let cumulativeProbability = 0

  for (const prize of prizes.value) {
    cumulativeProbability += prize.probability
    if (random <= cumulativeProbability) {
      return prize
    }
  }

  return prizes.value[prizes.value.length - 1] // Fallback
}

async function spinWheel() {
  if (isSpinning.value || dailySpins.value >= maxDailySpins.value) return

  isSpinning.value = true
  dailySpins.value++

  // Select prize based on probability
  const wonPrize = selectPrizeBasedOnProbability()
  currentPrize.value = wonPrize

  // Spin the wheel
  await wheel.spin(wonPrize)

  // Award points and save progress
  user.value.points += wonPrize.points
  addToRecentWins(wonPrize)

  // Show prize modal
  showPrizeModal.value = true
  isSpinning.value = false

  // Save data
  saveUserData()
  saveRecentWins()

  // Animate modal entrance
  nextTick(() => {
    if (prizeModal.value) {
      gsap.fromTo(prizeModal.value,
          { scale: 0, rotation: -10 },
          { scale: 1, rotation: 0, duration: 0.8, ease: "elastic.out(1, 0.5)" }
      )
    }
  })
}

function watchAdForSpin() {
  showAdModal.value = true
  adCountdown.value = 30
  canSkipAd.value = false

  adInterval = setInterval(() => {
    adCountdown.value--

    if (adCountdown.value <= 25) {
      canSkipAd.value = true
    }

    if (adCountdown.value <= 0) {
      completeAd()
    }
  }, 1000)
}

function skipAd() {
  if (canSkipAd.value) {
    completeAd()
  }
}

function completeAd() {
  if (adInterval) {
    clearInterval(adInterval)
    adInterval = null
  }

  showAdModal.value = false

  // Grant extra spin
  dailySpins.value = Math.max(0, dailySpins.value - 1)

  // Bonus points for watching ad
  user.value.points += 25
  saveUserData()
}

function addToRecentWins(prize: Prize) {
  const win: RecentWin = {
    id: Date.now().toString(),
    prize: prize.prize,
    emoji: prize.emoji,
    points: prize.points,
    timestamp: Date.now()
  }

  recentWins.value.unshift(win)

  // Keep only last 5 wins
  if (recentWins.value.length > 5) {
    recentWins.value = recentWins.value.slice(0, 5)
  }
}

function claimCoupon() {
  if (currentPrize.value?.type === 'coupon') {
    // Here you would integrate with your coupon system
    alert(`Coupon Code: ${currentPrize.value.couponCode}\nSaved to your account!`)

    // Add to user's coupons (you'd implement this)
    saveCouponToAccount(currentPrize.value)
  }

  closePrizeModal()
}

function saveCouponToAccount(prize: Prize) {
  // Implementation for saving coupon to user account
  const coupons = JSON.parse(localStorage.getItem('user-coupons') || '[]')
  coupons.push({
    code: prize.couponCode,
    value: prize.couponValue,
    type: prize.prize,
    claimedAt: Date.now(),
    expiresAt: Date.now() + (30 * 24 * 60 * 60 * 1000) // 30 days
  })
  localStorage.setItem('user-coupons', JSON.stringify(coupons))
}

function closePrizeModal() {
  showPrizeModal.value = false
  currentPrize.value = null
}

function loadUserData() {
  const saved = localStorage.getItem('spin-user-data')
  if (saved) {
    const data = JSON.parse(saved)
    user.value = { ...user.value, ...data }
  }

  // Load daily spins (reset daily)
  const today = new Date().toDateString()
  const savedSpins = localStorage.getItem('daily-spins')
  const savedDate = localStorage.getItem('daily-spins-date')

  if (savedDate === today && savedSpins) {
    dailySpins.value = parseInt(savedSpins)
  } else {
    dailySpins.value = 0
    localStorage.setItem('daily-spins-date', today)
  }
}

function saveUserData() {
  localStorage.setItem('spin-user-data', JSON.stringify({
    name: user.value.name,
    level: user.value.level,
    points: user.value.points
  }))

  localStorage.setItem('daily-spins', dailySpins.value.toString())
}

function loadRecentWins() {
  const saved = localStorage.getItem('recent-wins')
  if (saved) {
    recentWins.value = JSON.parse(saved)
  }
}

function saveRecentWins() {
  localStorage.setItem('recent-wins', JSON.stringify(recentWins.value))
}
</script>
