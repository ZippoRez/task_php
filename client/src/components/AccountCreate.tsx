import React from 'react';
import { Account } from '../types/Account'; // Импортируем тип Account
import AccountForm from './AccountForm'; // Импортируем компонент AccountForm

// Компонент AccountCreate для создания нового аккаунта
const AccountCreate: React.FC = () => {
  // Возвращаем компонент AccountForm в режиме создания (mode="create")
  return (
    <AccountForm mode="create" /> 
  );
};

export default AccountCreate;