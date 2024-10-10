<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const form = reactive({
    supplier_id: null,
    nomenclatures: [{ id: null, quantity: 0, price: 0, unit: '' }]
});

const units = ['шт.', 'кг.', 'л.'];

let isEdit = ref(false);
let isEditId = ref(0);
let isOpenModal = ref(false);
let messageResponse = ref('');
let nomenclatures = ref([]);
let supplies = ref([]);
const isLoading = ref(true); // Добавляем состояние загрузки

const totalPrice = computed(() => {
    return calculateTotalPrice(form.quantity, form.nomenclatureId);
});

onMounted(() => {
    Promise.all([getNomenclatures(), getSupplies()]).then(() => {
        isLoading.value = false; // Отключаем прелоадер после загрузки всех данных
    });
});

function getNomenclatures() {
    return axios.get('/get-nomenclatures').then((response) => {
        nomenclatures.value = response.data.nomenclatures;
    });
}

function getSupplies() {
    return axios.get('/get-supplies').then((response) => {
        supplies.value = response.data.supplies;
    });
}

function addSupply() {
    axios.post('/add-supply', {
        nomenclature_id: form.nomenclatureId,
        supply_date: form.supplyDate,
        quantity: form.quantity,
        unit: form.unit
    }).then((response) => {
        updateTable(response.data.status);
    }).catch(error => {
        console.error('Error adding supply:', error);
    });
}

function updateSupply() {
    axios.post('/update-supply', { ...form, supplyId: isEditId.value }).then((response) => {
        updateTable(response.data.status);
    });
}

function deleteSupply(id) {
    if (confirm('Вы действительно хотите удалить эту поставку?')) {
        axios.post('/delete-supply', { supplyId: id }).then((response) => {
            updateTable(response.data.status);
        });
    }
}

function updateTable(message) {
    isEdit.value = false;
    isEditId.value = 0;
    isOpenModal.value = true;
    messageResponse.value = message;
    getSupplies();
    resetForm();
    setTimeout(() => isOpenModal.value = false, 2000);
}

function resetForm() {
    Object.keys(form).forEach(key => form[key] = null);
    form.unit = 'шт.';
}

function editSupply(supply) {
    isEdit.value = true;
    isEditId.value = supply.id;
    form.nomenclatureId = supply.nomenclature_id;
    form.supplyDate = supply.supply_date;
    form.quantity = supply.quantity;
    form.unit = supply.unit;
    form.price = supply.price;
    
}

function calculateTotalPrice(quantity, nomenclatureId) {
    return quantity * (nomenclatures.value.find(n => n.id === nomenclatureId)?.price_per_unit || 0);
}

function cancelEdit() {
    if (confirm('Вы уверены, что хотите отменить редактирование? Несохраненные изменения будут потеряны.')) {
        isEdit.value = false;
        isEditId.value = 0;
        resetForm();
    }
}

function isWholeNumber(unit) {
    return unit === 'шт.';
}

function validateQuantity(form) {
    if (isWholeNumber(form.unit)) {
        form.quantity = Math.floor(form.quantity);
    }
}
</script>

<template>
    <AppLayout title="Supplies">
        <div class="modalMessage" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Поставки
            </h2>
        </template>

        <div v-if="!isLoading" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">{{ isEdit ? 'Редактировать поставку' : 'Добавить новую поставку' }}</h3>
                    <form @submit.prevent >
                        <div class="mb-4">
                            <label for="nomenclatureId" class="block text-sm font-medium text-gray-700">Номенклатура</label>
                            <select id="nomenclatureId" v-model="form.nomenclatureId" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="item in nomenclatures" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="supplyDate" class="block text-sm font-medium text-gray-700">Дата поставки</label>
                            <input type="date" id="supplyDate" v-model="form.supplyDate" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="unit" class="block text-sm font-medium text-gray-700">Единица измерения</label>
                            <select id="unit" v-model="form.unit" required
                                    @change="validateQuantity(form)"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                                <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Количество</label>
                            <input type="number" id="quantity" v-model="form.quantity" 
                                   :step="isWholeNumber(form.unit) ? 1 : 0.01"
                                   @input="validateQuantity(form)"
                                   required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <div class="mb-4">
                            <label for="totalPrice" class="block text-sm font-medium text-gray-700">Общая цена</label>
                            <input type="text" id="totalPrice" :value="totalPrice" readonly
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                       


                        <button v-if="!isEdit" type="submit" @click.prevent =" addSupply()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Добавить поставку
                        </button>
                        <button v-else type="submit" @click.prevent="updateSupply()"
                                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Изменить
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5 mt-5">
                    <h3 class="text-lg font-medium mb-4">Список поставок</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номенклатура</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количество</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена (₽)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="supply in supplies" :key="supply.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ supply.nomenclature.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ supply.supply_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ supply.quantity }} {{ supply.unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ supply.price }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a @click="editSupply(supply)" class="text-indigo-600 hover:text-indigo-900 cursor-pointer">Редактировать</a>
                                    <a @click="deleteSupply(supply.id)" class="ml-2 text-red-600 hover:text-red-900 cursor-pointer">Удалить</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div v-else class="preloader">
            <div class="loader"></div>
        </div>
    </AppLayout>
</template>

<style scoped>
.modalMessage {
    position: fixed;
    top: 10%;
    border: 1px solid #ccc;
    box-shadow: 0px 0px 20px #444;
    background-color: rgb(0, 95, 13);
    padding: 20px 40px;
    color: #ccc;
}
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}
.loader {
    border: 16px solid #f3f3f3;
    border-radius: 50%;
    border-top: 16px solid #3498db;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>