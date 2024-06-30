import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
  Paper, Button, Typography, Pagination, Select, MenuItem, FormControl, InputLabel,
  SelectChangeEvent, Dialog, DialogTitle, DialogContent, DialogActions,
} from '@mui/material';
import config from '../config';

interface Company {
  id: number;
  name: string;
  address: string;
}

const CompanyList: React.FC = () => {
  const [companies, setCompanies] = useState<Company[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Состояния для пагинации
  const [currentPage, setCurrentPage] = useState(1);
  const [companiesPerPage, setCompaniesPerPage] = useState(10);
  const [totalCompanies, setTotalCompanies] = useState(0);

  // Состояние для диалога подтверждения удаления
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [companyIdToDelete, setCompanyIdToDelete] = useState<number | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${config.apiUrl}/companies.php?page=${currentPage}&limit=${companiesPerPage}`);
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных');
        }
        const data = await response.json();
        setCompanies(data.data);
        setTotalCompanies(data.pagination.totalItems);
      } catch (err) {
        setError('Ошибка при загрузке данных');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [currentPage, companiesPerPage]);

  const handleDelete = (id: number) => {
    setCompanyIdToDelete(id);
    setDeleteDialogOpen(true);
  };

  const handleConfirmDelete = async () => {
    setDeleteDialogOpen(false);

    if (companyIdToDelete !== null) {
      try {
        const response = await fetch(`${config.apiUrl}/company.php?id=${companyIdToDelete}`, {
          method: 'DELETE',
        });

        if (!response.ok) {
          throw new Error('Ошибка при удалении компании');
        }

        // Обновляем список компаний
        setCompanies(companies.filter(company => company.id !== companyIdToDelete)); 
      } catch (err) {
        setError('Ошибка при удалении компании');
        console.error(err);
      } finally {
        setCompanyIdToDelete(null);
      }
    }
  };

  const handleCancelDelete = () => {
    setDeleteDialogOpen(false);
    setCompanyIdToDelete(null);
  };

  const handleChangePage = (event: React.ChangeEvent<unknown>, newPage: number) => {
    setCurrentPage(newPage);
  };

  const handleChangeRowsPerPage = (event: SelectChangeEvent<number>) => {
    setCompaniesPerPage(Number(event.target.value));
    setCurrentPage(1); 
  };

  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>;
  }

  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>;
  }

  return (
    <>
      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>Название</TableCell>
              <TableCell>Адрес</TableCell>
              <TableCell>Действия</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {companies.map((company) => (
              <TableRow key={company.id}>
                <TableCell>{company.id}</TableCell>
                <TableCell>{company.name}</TableCell>
                <TableCell>{company.address}</TableCell>
                <TableCell>
                  <Button component={Link} to={`/companies/${company.id}`}>
                    Просмотреть
                  </Button>
                  <Button component={Link} to={`/companies/edit/${company.id}`}>
                    Редактировать
                  </Button>
                  <Button onClick={() => handleDelete(company.id)} color="error">
                    Удалить
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '20px' }}>
        <FormControl variant="filled" sx={{ width: 1/10 }}>
          <InputLabel id="companies-per-page-label">Компаний на странице</InputLabel>
          <Select
            labelId="companies-per-page-label"
            id="companies-per-page"
            value={companiesPerPage}
            onChange={handleChangeRowsPerPage}
            label="Компаний на странице"
          >
            <MenuItem value="10">10</MenuItem>
            <MenuItem value="25">25</MenuItem>
            <MenuItem value="50">50</MenuItem>
          </Select>
        </FormControl>
        <div style={{ marginTop: '20px' }}>
        <Button component={Link} to="/" variant="contained" color="primary" style={{ marginLeft: '10px' }}>
            Список аккаунтов
          </Button>
        <Button component={Link} to="/companies/create" variant="contained" color="secondary" style={{ marginLeft: '10px' }}>
          Создать компанию
        </Button>
      </div>
        <Pagination count={Math.ceil(totalCompanies / companiesPerPage)} page={currentPage} onChange={handleChangePage} />
        
      </div>
      

      {/* Диалог подтверждения удаления */}
      <Dialog open={deleteDialogOpen} onClose={handleCancelDelete}>
        <DialogTitle>Подтверждение удаления</DialogTitle>
        <DialogContent>
          <Typography>Вы уверены, что хотите удалить эту компанию?</Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCancelDelete}>Отмена</Button>
          <Button onClick={handleConfirmDelete} color="error">
            Удалить
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default CompanyList;