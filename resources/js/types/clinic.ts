export type ClinicUser = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'patient';
};
export type Polyclinic = {
    id: number;
    name: string;
    code: string;
    room_location: string;
    base_admin_fee: number;
    is_active: boolean;
};
export type Doctor = {
    id: number;
    name: string;
    sip_number: string;
    photo: string | null;
    polyclinic_id: number;
    polyclinic: Polyclinic;
    is_active: boolean;
};
export type Schedule = {
    id: number;
    doctor_id: number;
    doctor: Doctor;
    day_of_week: number;
    start_time: string;
    end_time: string;
    max_quota: number;
    is_active: boolean;
    closed_date: string | null;
    remaining?: number;
    available?: boolean;
    unavailable_reason?: string | null;
};
export type Patient = {
    id: number;
    user_id: number;
    user: ClinicUser;
    no_rm: string;
    nik: string;
    birth_date: string;
    gender: string;
    blood_type: string | null;
    phone: string;
    address: string;
    reservations_count?: number;
};
export type Payment = {
    id: number;
    admin_fee: number;
    consultation_fee: number;
    total_amount: number;
    payment_method: string;
    paid_at: string;
    payment_status: string;
};
export type Reservation = {
    id: number;
    booking_code: string;
    patient_id: number;
    patient: Patient;
    schedule_id: number;
    schedule: Schedule;
    reservation_date: string;
    queue_number: number;
    complaint: string;
    booking_type: string;
    status: string;
    admin_fee: number;
    transaction: Payment | null;
};
export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};
export type Pagination<T> = {
    data: T[];
    links: PaginationLink[];
    total: number;
};
export type Monitor = {
    id: number;
    number: number;
    doctor: string;
    polyclinic: string;
    room: string;
};
export const days = [
    'Minggu',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
];
export const money = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
export const dateLabel = (value: string) =>
    new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(
        new Date(value.slice(0, 10) + 'T12:00:00'),
    );
export const queueLabel = (value: number) => String(value).padStart(3, '0');
export const statuses: Record<string, string> = {
    booked: 'Dipesan',
    waiting: 'Menunggu',
    examining: 'Diperiksa',
    completed: 'Selesai',
    skipped: 'Dilewati',
    cancelled: 'Dibatalkan',
};
