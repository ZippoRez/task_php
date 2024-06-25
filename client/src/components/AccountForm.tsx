// AccountForm.tsx

import React from 'react';
import { Button, Grid } from '@mui/material';
import { Account } from '../types/Account'; 
import InputTextField from './InputTextField';

// Интерфейс для свойств компонента AccountForm
interface AccountFormProps {
  formData: Account; // Данные аккаунта (для заполнения полей формы)
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void; // Обработчик изменения значения в поле формы
  onSubmit: (event: React.FormEvent) => void; // Обработчик отправки формы
}

// Компонент AccountForm - форма для редактирования/создания аккаунта
const AccountForm: React.FC<AccountFormProps> = ({ formData, onChange, onSubmit }) => (
  // Форма 
  <form onSubmit={onSubmit}>
    {/* Grid для организации полей формы */}
    <Grid container spacing={2}>
      {/* Поле "Имя" */}
      <InputTextField
        label="Имя" 
        name="first_name" 
        value={formData.first_name} 
        onChange={onChange} 
        required 
      />

      {/* Поле "Фамилия" */}
      <InputTextField
        label="Фамилия"
        name="last_name"
        value={formData.last_name}
        onChange={onChange}
        required
      />

      {/* Поле "Email" */}
      <InputTextField
        label="Email"
        name="email"
        value={formData.email}
        onChange={onChange}
        required
      />

      {/* Поле "Название компании" */}
      <InputTextField
        label="Название компании"
        name="company_name"
        value={formData.company_name || ''} // Если company_name пустое, устанавливаем пустую строку
        onChange={onChange}
      />

      {/* Поле "Должность" */}
      <InputTextField
        label="Должность"
        name="position"
        value={formData.position || ''} // Если position пустое, устанавливаем пустую строку
        onChange={onChange}
      />

      {/* Поле "Телефон 1" */}
      <InputTextField
        label="Телефон 1"
        name="phone_1"
        value={formData.phone_1 || ''} // Если phone_1 пустое, устанавливаем пустую строку
        onChange={onChange}
      />

      {/* Поле "Телефон 2" */}
      <InputTextField
        label="Телефон 2"
        name="phone_2"
        value={formData.phone_2 || ''} // Если phone_2 пустое, устанавливаем пустую строку
        onChange={onChange}
      />

      {/* Поле "Телефон 3" */}
      <InputTextField
        label="Телефон 3"
        name="phone_3"
        value={formData.phone_3 || ''} // Если phone_3 пустое, устанавливаем пустую строку
        onChange={onChange}
      />
    </Grid>

    {/* Кнопка "Сохранить" для отправки формы */}
    <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
      Сохранить
    </Button>
  </form>
);

export default AccountForm; 