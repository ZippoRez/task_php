export interface Account {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    company_name: string | null;
    position: string | null;
    phone_1: string | null;
    phone_2: string | null;
    phone_3: string | null;
}