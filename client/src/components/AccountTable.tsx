import React from 'react';
import {
  Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
  Paper 
} from '@mui/material'; // Импортируем компоненты Material-UI для таблицы
import AccountRow from './AccountRow'; // Импортируем компонент строки таблицы
import { Account } from '../types/Account'; // Импортируем тип данных Account

// Интерфейс свойств для компонента AccountTable
interface AccountTableProps {
  accounts: Account[]; // Массив аккаунтов для отображения
  onDelete: (id: number) => void; // Функция для удаления аккаунта
  onRestore?: (id: number) => void; // Функция для восстановления аккаунта (необязательная)
}

// Компонент AccountTable для отображения таблицы аккаунтов
const AccountTable: React.FC<AccountTableProps> = ({ accounts, onDelete, onRestore }) => (
  // Контейнер таблицы с использованием Paper для стилизации
  <TableContainer component={Paper} sx={{ width: '100%' }}> 
    {/* Таблица */}
    <Table size="medium">
      {/* Заголовок таблицы */}
      <TableHead>
        <TableRow>
          {/* Добавляем пустую ячейку в заголовок, если есть функция onRestore (для кнопки "Восстановить") */}
          {onRestore &&  
            <TableCell></TableCell> 
          }
          {/* Ячейки заголовка */}
          <TableCell>ID</TableCell>
          <TableCell>Имя</TableCell>
          <TableCell>Фамилия</TableCell>
          <TableCell>Email</TableCell>
          <TableCell>Должность</TableCell>
          <TableCell>Телефон 1</TableCell>
          <TableCell>Телефон 2</TableCell>
          <TableCell>Телефон 3</TableCell>
          <TableCell>Компания</TableCell>
          <TableCell>Действия</TableCell>
        </TableRow>
      </TableHead>

      {/* Тело таблицы */}
      <TableBody>
        {/* Отображение строк таблицы для каждого аккаунта */}
        {accounts.map(account => ( 
          // Компонент AccountRow для каждой строки
          <AccountRow 
            key={account.id} // Уникальный ключ для каждой строки
            account={account} // Передаем данные аккаунта в AccountRow
            onDelete={onDelete} // Передаем функцию удаления в AccountRow
            onRestore={onRestore} // Передаем функцию восстановления в AccountRow (если она есть)
          /> 
        ))}
      </TableBody>
    </Table>
  </TableContainer>
);

export default AccountTable;