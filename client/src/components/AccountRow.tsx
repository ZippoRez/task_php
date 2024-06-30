import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { TableRow, TableCell, Button, Typography } from '@mui/material';
import { Account } from '../types/Account';
import { formatDistanceToNow, differenceInSeconds } from 'date-fns';
import { ru } from 'date-fns/locale';
import config from '../config'; 

interface AccountRowProps {
  account: Account;
  onDelete: (id: number) => void;
  onRestore?: (id: number) => void;
}

const AccountRow: React.FC<AccountRowProps> = ({
  account,
  onDelete,
  onRestore,
}) => {
  const timeUntilDeletion = account.deleted_at
    ? 72 * 60 * 60 -
      differenceInSeconds(new Date(), new Date(account.deleted_at))
    : null;

  const [companyName, setCompanyName] = useState('');

  useEffect(() => {
    const fetchCompanyName = async () => {
      if (account.company_id) {
        try {
          const response = await fetch(`${config.apiUrl}/company.php?id=${account.company_id}`);
          if (!response.ok) {
            throw new Error('Ошибка при загрузке данных компании');
          }
          const data = await response.json();
          setCompanyName(data.data.name);
        } catch (err) {
          console.error('Ошибка при получении названия компании:', err);
        }
      }
    };

    fetchCompanyName();
  }, [account.company_id]);
  // console.log(account);
  return (
    <TableRow key={account.id}>
      {account.deleted_at && onRestore && (
      <TableCell>
          <Typography variant="caption" color="textSecondary">
            Удалено{' '}
            {formatDistanceToNow(new Date(account.deleted_at), {
              addSuffix: true,
              locale: ru,
            })}
            {timeUntilDeletion !== null && timeUntilDeletion > 0 && (
              <span>
                , остаётся:{' '}
                {formatDistanceToNow(new Date(Date.now() + timeUntilDeletion * 1000), { locale: ru })}
              </span>
            )}
          </Typography>
      </TableCell>
      )}
      <TableCell>{account.id}</TableCell>
      <TableCell>{account.first_name}</TableCell>
      <TableCell>{account.last_name}</TableCell>
      <TableCell>{account.email}</TableCell>
      <TableCell>{account.position}</TableCell>
      <TableCell>{account.phone_1}</TableCell>
      <TableCell>{account.phone_2}</TableCell>
      <TableCell>{account.phone_3}</TableCell>
      <TableCell>
        {account.company_id ? (
          <Link to={`/companies/${account.company_id}`}>
            {companyName}
          </Link>
        ) : (
          '-'
        )}
      </TableCell>
      <TableCell>
        {!account.deleted_at && (
          <>
            <Button component={Link} to={`/edit/${account.id}`}>
              Редактировать
            </Button>
          </>
        )}

        {account.deleted_at && onRestore && (
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