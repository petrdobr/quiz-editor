<template>
  <div class="container py-4">
    <h2>Редактор теста</h2>

    <!-- Вопрос -->
    <div class="mb-3">
      <label class="form-label">Вопрос</label>
      <input type="text" class="form-control" v-model="question" :class="{'is-invalid': showErrors && !question.trim()}">
      <div class="invalid-feedback">Введите вопрос</div>
    </div>

    <!-- Переключатель multiple -->
    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" id="multipleCheck" v-model="multiple">
      <label class="form-check-label" for="multipleCheck">Разрешить несколько правильных ответов</label>
    </div>

    <!-- Варианты ответа -->
    <div v-for="(option, index) in options" :key="index" class="mb-2 d-flex align-items-center">
      <span class="me-2">{{ multiple ? '✓' : index + 1 }}.</span>
      <input type="text" class="form-control me-2" v-model="option.text" :class="{'is-invalid': showErrors && !option.text.trim()}">
      <input :type="multiple ? 'checkbox' : 'radio'" :name="'correct'" v-model="option.correct" :checked="option.correct" @change="toggleCorrect(index)">
      <button class="btn btn-danger btn-sm ms-2" @click="removeOption(index)" :disabled="options.length <= 2">×</button>
      <div class="invalid-feedback d-block" v-if="showErrors && !option.text.trim()">Заполните вариант</div>
    </div>

    <!-- Кнопка добавить вариант -->
    <div class="mb-3">
      <button class="btn btn-secondary" @click="addOption" :disabled="options.length >= 6">Добавить вариант</button>
    </div>

    <!-- Кнопки действий -->
    <div class="mb-3">
      <button class="btn btn-primary me-2" @click="preview">Предпросмотр</button>
      <button class="btn btn-outline-primary" @click="getJSON">Получить JSON</button>
    </div>

    <!-- Блок предпросмотра -->
    <div v-if="showPreview" class="border p-3 bg-light">
      <h5>Предпросмотр</h5>
      <p><strong>{{ question }}</strong></p>
      <div v-for="(option, idx) in options" :key="'p' + idx" class="form-check">
        <input :type="multiple ? 'checkbox' : 'radio'" :name="'preview'" class="form-check-input" disabled>
        <label class="form-check-label">{{ option.text }}</label>
      </div>
    </div>

    <!-- JSON вывод -->
    <div v-if="showJSON" class="mt-4">
      <h5>JSON задания</h5>
      <pre>{{ JSON.stringify(outputJSON, null, 2) }}</pre>
    </div>
  </div>
  <!-- Кнопка сохранить -->
<div class="mb-3">
  <button class="btn btn-success" @click="saveAssignment">Сохранить задание</button>
</div>

<!-- Сообщения -->
<div v-if="saveSuccess" class="alert alert-success">Задание сохранено</div>
<div v-if="error" class="alert alert-danger">Ошибка: {{ error }}</div>
<div v-if="loading" class="alert alert-info">Загрузка...</div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  assignmentId: Number,
  isEdit: Boolean
})

// state
const question = ref('')
const multiple = ref(false)
const showPreview = ref(false)
const showJSON = ref(false)
const showErrors = ref(false)
const loading = ref(false)
const error = ref(null)
const saveSuccess = ref(false)

const router = useRouter()

const options = reactive([
  { text: '', correct: false },
  { text: '', correct: false }
])

// загрузка задания по ID
async function loadAssignment() {
  if (!props.assignmentId || !props.isEdit) return

  loading.value = true
  try {
    const res = await fetch(`http://localhost:8001/api/assignments/${props.assignmentId}`)
    if (!res.ok) throw new Error('Ошибка загрузки задания')
    const data = await res.json()

    question.value = data.spec.question
    multiple.value = data.spec.multiple
    options.splice(0, options.length, ...data.spec.options.map((text, i) => ({
      text,
      correct: data.spec.correct.includes(i)
    })))
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

onMounted(loadAssignment)

function addOption() {
  if (options.length < 6) options.push({ text: '', correct: false })
}

function removeOption(index) {
  options.splice(index, 1)
}

function toggleCorrect(index) {
  if (!multiple.value) {
    options.forEach((opt, i) => opt.correct = i === index)
  } else {
    options[index].correct = !options[index].correct
  }
}

function validate() {
  return (
    question.value.trim() &&
    options.length >= 2 &&
    options.length <= 6 &&
    options.every(opt => opt.text.trim()) &&
    options.some(opt => opt.correct)
  )
}

const outputJSON = computed(() => ({
  type: 'quiz',
  question: question.value,
  options: options.map(o => o.text),
  correct: options.map((o, i) => o.correct ? i : -1).filter(i => i !== -1),
  multiple: multiple.value
}))

function preview() {
  showErrors.value = true
  if (validate()) {
    showPreview.value = true
    showJSON.value = false
  }
}

function getJSON() {
  showErrors.value = true
  if (validate()) {
    showJSON.value = true
    showPreview.value = false
    console.log('JSON:', outputJSON.value)
  }
}

async function saveAssignment() {
  showErrors.value = true
  if (!validate()) return

  const payload = {
    title: question.value.substring(0, 30) || 'Без названия',
    type: 'quiz',
    spec: outputJSON.value
  }

  try {
    const url = props.isEdit
      ? `http://localhost:8001/api/assignments/${props.assignmentId}`
      : `http://localhost:8001/api/assignments`
    const method = props.isEdit ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!res.ok) throw new Error('Ошибка сохранения задания')
    const data = await res.json()
    saveSuccess.value = true
    console.log('Сохранено:', data)
    resetForm()
    router.push({ name: 'assignment-list' })
  } catch (err) {
    error.value = err.message
  }
}

function resetForm() {
  question.value = ''
  multiple.value = false
  showPreview.value = false
  showJSON.value = false
  showErrors.value = false
  options.splice(0, options.length, { text: '', correct: false }, { text: '', correct: false })
  saveSuccess.value = false
  error.value = null
}
</script>

