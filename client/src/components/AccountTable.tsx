import React from 'react';
import {
  Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
  Paper 
} from '@mui/material'; //  Компоненты Material-UI
import AccountRow from './AccountRow'; //  Компонент строки таблицы
import { Account } from '../types/Account'; //  Тип данных Account

//  Интерфейс свойств компонента AccountTable
interface AccountTableProps {
  accounts: Account[]; //  Массив аккаунтов для отображения в таблице
  onDelete: (id: number) => void; //  Функция,  вызываемая при нажатии на кнопку "Удалить" в строке
  onRestore?: (id: number) => void; //  Функция,  вызываемая при нажатии на кнопку "Восстановить" (необязательная)
}

const AccountTable: React.FC<AccountTableProps> = ({ accounts, onDelete, onRestore }) => (
  //  Контейнер таблицы с использованием Paper для стилизации
  <TableContainer component={Paper} sx={{ width: '100%' }}> 
    {/*  Таблица  */}
    <Table size="medium">
      {/*  Заголовок таблицы */}
      <TableHead>
        <TableRow>
          {/*  Добавляем пустую ячейку в заголовок, если есть функция onRestore  */}
          {onRestore &&  
            <TableCell></TableCell> 
          }
          <TableCell>ID</TableCell>
          <TableCell>Имя</TableCell>
          <TableCell>Фамилия</TableCell>
          <TableCell>Email</TableCell>
          <TableCell>Компания</TableCell>
          <TableCell>Должность</TableCell>
          <TableCell>Телефон 1</TableCell>
          <TableCell>Телефон 2</TableCell>
          <TableCell>Телефон 3</TableCell>
          <TableCell>Действия</TableCell>
        </TableRow>
      </TableHead>

      {/*  Тело таблицы */}
      <TableBody>
        {/*  Отображаем строки таблицы для каждого аккаунта */}
        {accounts.map(account => ( 
          <AccountRow 
            key={account.id} //  Уникальный ключ для каждой строки
            account={account} //  Передаем данные аккаунта в строку
            onDelete={onDelete} //  Передаем функцию onDelete в строку
            onRestore={onRestore} //  Передаем функцию onRestore в строку, если она есть
          /> 
        ))}
      </TableBody>
    </Table>
  </TableContainer>
);

export default AccountTable; 