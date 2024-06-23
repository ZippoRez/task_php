// AccountForm.tsx

import React from 'react';
import { Button, Grid } from '@mui/material';
import { Account } from '../types/Account';
import InputTextField from './InputTextField';

interface AccountFormProps {
  formData: Account;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
  onSubmit: (event: React.FormEvent) => void;
}

const AccountForm: React.FC<AccountFormProps> = ({ formData, onChange, onSubmit }) => (
    <form onSubmit={onSubmit}>
    <Grid container spacing={2}>
      <InputTextField
        label="Имя"
        name="first_name"
        value={formData.first_name}
        onChange={onChange}
        required
      />
      <InputTextField
        label="Фамилия"
        name="last_name"
        value={formData.last_name}
        onChange={onChange}
        required
      />
      <InputTextField
        label="Email"
        name="email"
        value={formData.email}
        onChange={onChange}
        required
      />
      <InputTextField
        label="Название компании"
        name="company_name"
        value={formData.company_name || ''}
        onChange={onChange}
      />
      <InputTextField
        label="Должность"
        name="position"
        value={formData.position || ''}
        onChange={onChange}
      />
      <InputTextField
        label="Телефон 1"
        name="phone_1"
        value={formData.phone_1 || ''}
        onChange={onChange}
      />
      <InputTextField
        label="Телефон 2"
        name="phone_2"
        value={formData.phone_2 || ''}
        onChange={onChange}
      />
      <InputTextField
        label="Телефон 3"
        name="phone_3"
        value={formData.phone_3 || ''}
        onChange={onChange}
      />
    </Grid>

    <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
      Сохранить
    </Button>
  </form>
);

export default AccountForm;