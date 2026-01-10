<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    invoices: Array,
    proposals: Array,
});

const invoicesList = ref([...props.invoices]); // Make reactive for Echo updates

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

// Payment Modal
const showPaymentModal = ref(false);
const selectedInvoice = ref(null);
const paymentForm = useForm({
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
});

const openPaymentModal = (invoice) => {
    selectedInvoice.value = invoice;
    paymentForm.amount = invoice.balance_due; // Default to full balance
    showPaymentModal.value = true;
};

const submitPayment = () => {
    paymentForm.post(route('payments.store', selectedInvoice.value.id), {
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
        },
    });
};

const convertProposal = (proposal) => {
    if (confirm('¿Aceptar propuesta y convertir a proyecto?')) {
        router.post(route('proposals.convert', proposal.id));
    }
};

// Real-time Updates
onMounted(() => {
    // Listen to dashboard/finance channel if configured
    window.Echo.private('finance.dashboard')
        .listen('PaymentRegistered', (e) => {
             // Find and update invoice in list
             const index = invoicesList.value.findIndex(inv => inv.id === e.invoice_id);
             if (index !== -1) {
                 invoicesList.value[index].balance_due = e.new_balance;
                 invoicesList.value[index].status = e.status;
             }
        });
});

</script>

<template>
    <Head title="Finanzas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Finanzas</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-10">
                
                <!-- Proposals Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Propuestas Recientes</h3>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titulo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="proposal in proposals" :key="proposal.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ proposal.title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ proposal.company?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(proposal.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ proposal.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button v-if="proposal.status === 'draft' || proposal.status === 'sent'" @click="convertProposal(proposal)" class="text-brand hover:text-indigo-900">Convertir</button>
                                    </td>
                                </tr>
                                <tr v-if="proposals.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No hay propuestas recientes.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Invoices Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Facturas</h3>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numero</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Pendiente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="invoice in invoicesList" :key="invoice.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ invoice.number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(invoice.total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">{{ formatCurrency(invoice.balance_due) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{'bg-green-100 text-green-800': invoice.status === 'paid', 'bg-red-100 text-red-800': invoice.balance_due > 0}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ invoice.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button v-if="invoice.balance_due > 0" @click="openPaymentModal(invoice)" class="text-brand hover:text-indigo-900">Registrar Pago</button>
                                    </td>
                                </tr>
                                 <tr v-if="invoicesList.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay facturas registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Registrar Pago</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Factura {{ selectedInvoice?.number }} - Saldo: {{ formatCurrency(selectedInvoice?.balance_due) }}
                </p>

                <div class="mt-6">
                    <InputLabel for="amount" value="Monto a Pagar" />
                    <TextInput
                        id="amount"
                        v-model="paymentForm.amount"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                        placeholder="0.00"
                    />
                </div>

                 <div class="mt-4">
                    <InputLabel for="date" value="Fecha de Pago" />
                    <TextInput
                        id="date"
                        v-model="paymentForm.payment_date"
                        type="date"
                        class="mt-1 block w-full"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showPaymentModal = false"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submitPayment" :class="{ 'opacity-25': paymentForm.processing }" :disabled="paymentForm.processing">
                        Guardar Pago
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
