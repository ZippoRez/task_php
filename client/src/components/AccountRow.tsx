import React from 'react';
import { Link } from 'react-router-dom';
import { TableRow, TableCell, Button, Typography } from '@mui/material';
import { Account } from '../types/Account';
import { differenceInSeconds, formatDistanceToNow } from 'date-fns';

interface AccountRowProps {
  account: Account;
  onDelete: (id: number) => void;
  onRestore?: (id: number) => void;
}

const AccountRow: React.FC<AccountRowProps> = ({ account, onDelete, onRestore }) => {
  const timeUntilDeletion = account.deleted_at ? 1 * 60 * 60 - differenceInSeconds(new Date(), new Date(account.deleted_at)) : null;
  return(
  <TableRow key={account.id}>
    <TableCell>{account.deleted_at && (
          <Typography variant="caption" color="textSecondary">
            Удалено {formatDistanceToNow(new Date(account.deleted_at), { addSuffix: true})}
            {timeUntilDeletion !== null && timeUntilDeletion > 0 && (
              <span>, остаётся: {formatDistanceToNow(new Date(Date.now() + timeUntilDeletion * 1000))}</span>
            )}
          </Typography>
        )}</TableCell>
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
      {!account.deleted_at &&  (
                  <><Button component={Link} to={`/edit/${account.id}`}>
          Редактировать
        </Button></>
                )}
      
      {onRestore && account.deleted_at &&  (
                  <Button onClick={() => onRestore(account.id)} color="success">
                      Восстановить
                  </Button>
                )}
      <Button onClick={() => onDelete(account.id)} color="error">
            Удалить
      </Button>
    </TableCell>
  </TableRow>
  );
};

export default AccountRow;