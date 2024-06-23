import React from 'react';
import { Link } from 'react-router-dom';
import { TableRow, TableCell, Button } from '@mui/material';
import { Account } from '../types/Account';

interface AccountRowProps {
  account: Account;
  onDelete: (id: number) => void;
}

const AccountRow: React.FC<AccountRowProps> = ({ account, onDelete }) => (
  <TableRow key={account.id}>
    <TableCell>{account.id}</TableCell>
    <TableCell>{account.first_name}</TableCell>
    <TableCell>{account.last_name}</TableCell>
    <TableCell>{account.email}</TableCell>
    <TableCell>{account.company_name}</TableCell>
    <TableCell>{account.position}</TableCell>
    <TableCell>{account.phone_1}</TableCell>
    <TableCell>{account.phone_2}</TableCell>
    <TableCell>{account.phone_3}</TableCell>
    <TableCell>
      <Button component={Link} to={`/edit/${account.id}`}>
        Редактировать
      </Button>
      <Button onClick={() => onDelete(account.id)} color="error">
        Удалить
      </Button>
    </TableCell>
  </TableRow>
);

export default AccountRow;