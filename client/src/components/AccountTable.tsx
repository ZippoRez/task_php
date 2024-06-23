import React from 'react';
import {
  Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
  Paper 
} from '@mui/material';
import AccountRow from './AccountRow';
import { Account } from '../types/Account';

interface AccountTableProps {
  accounts: Account[];
  onDelete: (id: number) => void;
  onRestore?: (id: number) => void;// Добавляем onRestore
}

const AccountTable: React.FC<AccountTableProps> = ({ accounts, onDelete, onRestore }) => (
  <TableContainer component={Paper} sx={{width: '100%'}}>
    <Table size="medium">
      <TableHead>
        <TableRow>
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
      <TableBody>
        {accounts.map(account => (
          <AccountRow key={account.id} account={account} onDelete={onDelete} onRestore={onRestore}/>
        ))}
      </TableBody>
    </Table>
  </TableContainer>
);

export default AccountTable;