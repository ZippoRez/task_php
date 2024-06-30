import React from 'react';
import { Button, Grid, FormControl, InputLabel, Select, MenuItem } from '@mui/material';
import { Account } from '../types/Account';
import InputTextField from './InputTextField';
import { SelectChangeEvent } from '@mui/material';
import { Company } from '../types/Company'; 
// Интерфейс для свойств компонента AccountForm
interface AccountFormProps {
  formData: Account; 
  companies: Company[];  
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void; 
  onSubmit: (event: React.FormEvent) => void; 
  onCompanyChange: (event: SelectChangeEvent<number>) => void; 
}

// Компонент AccountForm - форма для редактирования/создания аккаунта
const AccountForm: React.FC<AccountFormProps> = ({ formData, companies, onChange, onSubmit, onCompanyChange }) => (
  <form onSubmit={onSubmit}>
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
      {/* Select для выбора компании */}
      <Grid item xs={12}>
        <FormControl fullWidth>
          <InputLabel id="company-select-label">Компания</InputLabel>
          <Select
            labelId="company-select-label"
            id="company-select"
            name="company_id" 
            value={formData.company_id || ''} 
            onChange={onCompanyChange}
            label="Компания"
          >
            {companies.map((company) => (
              <MenuItem key={company.id} value={company.id.toString()}>
                {company.name}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </Grid>
    </Grid>

    <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
      Сохранить
    </Button>
  </form>
);

export default AccountForm;