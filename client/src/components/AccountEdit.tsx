import React from 'react';
import { useParams } from 'react-router-dom'; // Импортируем useParams для доступа к параметрам URL
import { Account } from '../types/Account'; // Импортируем тип Account
import AccountForm from './AccountForm'; // Импортируем компонент AccountForm

// Компонент AccountEdit для редактирования существующего аккаунта
const AccountEdit: React.FC = () => {
  // Получаем ID аккаунта из параметров URL
  const { id } = useParams<{ id: string }>(); 
  // Преобразуем ID из строки в число
  const accountId = id ? parseInt(id, 10) : 0; 

  // Возвращаем компонент AccountForm в режиме редактирования (mode="edit") 
  // и передаем ему ID аккаунта
  return (
    <AccountForm mode="edit" accountId={accountId} />
  );
};

export default AccountEdit;