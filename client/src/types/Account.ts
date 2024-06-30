//  Интерфейс Account определяет структуру данных аккаунта 
export interface Account {
    id: number; //  ID аккаунта (число)
    first_name: string; //  Имя (строка)
    last_name: string; //  Фамилия (строка)
    email: string; //  Email (строка)
    company_id: number | null; //  Компания
    // company_name: string | null; //  Название компании (строка или null,  если не указано)
    position: string | null; //  Должность (строка или null,  если не указано)
    phone_1: string | null; //  Телефон 1 (строка или null,  если не указано)
    phone_2: string | null; //  Телефон 2 (строка или null,  если не указано)
    phone_3: string | null; //  Телефон 3 (строка или null,  если не указано)
    deleted_at: string | null; //  Дата и время "мягкого" удаления
  }